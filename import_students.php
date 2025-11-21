<?php
/**
 * Excel to Students Table Importer - PREVIEW & IMPORT VERSION
 * Run with: php import_students.php preview   (to see what will be imported)
 * Run with: php import_students.php import    (to actually import)
 */

class StudentImporter {
    private $conn;
    private $imported = 0;
    private $skipped = 0;
    private $errors = [];
    private $previewData = [];
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function preview($excelFile) {
        echo "PREVIEW MODE - Analyzing Excel file...\n\n";
        $this->process($excelFile, true);
    }
    
    public function import($excelFile) {
        echo "IMPORT MODE - Beginning data import...\n\n";
        $this->process($excelFile, false);
    }
    
    private function process($excelFile, $previewOnly = false) {
        $zip = new ZipArchive();
        
        if ($zip->open($excelFile) !== TRUE) {
            die("Failed to open Excel file: $excelFile\n");
        }
        
        // Get shared strings
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $stringsXml = simplexml_load_string($sharedStringsXml);
        $strings = [];
        foreach ($stringsXml->si as $si) {
            $strings[] = (string)$si->t;
        }
        
        // Get worksheet data
        $xmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        $xml = simplexml_load_string($xmlContent);
        $zip->close();
        
        $totalRows = count($xml->sheetData->row);
        echo ($previewOnly ? "Analyzing" : "Starting import of") . " " . ($totalRows - 1) . " student records...\n\n";
        
        // Skip header row (row 0), start from row 1
        for ($r = 1; $r < $totalRows; $r++) {
            $row = $xml->sheetData->row[$r];
            $this->processRow($row, $strings, $r, $previewOnly);
            
            // Progress indicator every 500 rows
            if ($r % 500 === 0) {
                echo "Processed $r rows...\n";
            }
        }
        
        // Show preview of first few records if in preview mode
        if ($previewOnly) {
            echo "\n" . str_repeat("=", 120) . "\n";
            echo "SAMPLE OF RECORDS TO BE IMPORTED (first 5):\n";
            echo str_repeat("=", 120) . "\n";
            
            for ($i = 0; $i < min(5, count($this->previewData)); $i++) {
                $data = $this->previewData[$i];
                echo "\nRecord " . ($i + 1) . ":\n";
                foreach ($data as $key => $value) {
                    $displayValue = $value === null ? "(null)" : $value;
                    echo "  $key: $displayValue\n";
                }
            }
        }
        
        $this->printSummary($previewOnly);
    }
    
    private function processRow($row, $strings, $rowNum, $previewOnly = false) {
        $cells = [];
        $cellIndex = 0;
        
        // Extract cell values
        foreach ($row->c as $cell) {
            $cellValue = (string)$cell->v;
            $cellType = (string)$cell['t'];
            
            if ($cellType === 's') {
                $value = isset($strings[(int)$cellValue]) ? trim($strings[(int)$cellValue]) : '';
            } else {
                $value = trim($cellValue);
            }
            
            $cells[$cellIndex] = $value;
            $cellIndex++;
        }
        
        // Map Excel columns to database fields
        // Based on analysis: idno, lastname, firstname, middlei, course, year, age
        $studentID = $cells[0] ?? null;
        $firstName = $cells[2] ?? null;    
        $lastName = $cells[1] ?? null;     
        $middleName = $cells[3] ?? null;
        $course = $cells[4] ?? null;
        $yearLvl = $cells[5] ?? null;
        $age = $cells[6] ?? null;
        
        // Validation
        if (empty($studentID)) {
            $this->errors[] = "Row $rowNum: Missing Student ID";
            $this->skipped++;
            return;
        }
        
        // Generate BirthDate from age
        $birthDate = null;
        if (!empty($age) && is_numeric($age)) {
            $birthYear = date('Y') - (int)$age;
            $birthDate = $birthYear . '-01-01';
        } else {
            $birthDate = date('Y-m-d'); // Use today if no age
        }
        
        // Default values for missing fields
        $gender = 'Other';
        $department = 'COTE';
        $section = 'N/A';
        
        if ($previewOnly) {
            $this->previewData[] = [
                'StudentID' => $studentID,
                'FirstName' => $firstName,
                'MiddleName' => $middleName,
                'LastName' => $lastName,
                'Gender' => $gender . ' (default)',
                'BirthDate' => $birthDate,
                'Course' => $course,
                'YearLevel' => $yearLvl,
                'Section' => $section . ' (default)',
                'Department' => $department . ' (default)',
                'isActive' => 1,
                'IsEnroll' => 1
            ];
            $this->imported++;
        } else {
            $this->insertStudent(
                $studentID,
                $firstName,
                $middleName,
                $lastName,
                $gender,
                $birthDate,
                $course,
                $yearLvl,
                $section,
                $department,
                $rowNum
            );
        }
    }
    
    private function insertStudent($id, $fname, $mname, $lname, $gender, $birthdate, $course, $yearlvl, $section, $dept, $rowNum) {
        // Prepare values
        $id = $this->conn->real_escape_string(trim($id ?? ''));
        $fname = $this->conn->real_escape_string(trim($fname ?? 'N/A'));
        $mname = $this->conn->real_escape_string(trim($mname ?? ''));
        $lname = $this->conn->real_escape_string(trim($lname ?? 'N/A'));
        $course = $this->conn->real_escape_string(trim($course ?? 'Unknown'));
        $yearlvl = is_numeric($yearlvl) ? (int)$yearlvl : 1;
        $section = $this->conn->real_escape_string(trim($section ?? 'N/A'));
        $birthdate = $birthdate ?? date('Y-m-d');
        
        if (empty($id)) {
            $this->errors[] = "Row $rowNum: Empty Student ID (cannot import)";
            $this->skipped++;
            return;
        }
        
        // Check if student already exists
        $checkQuery = "SELECT StudentID FROM students WHERE StudentID = '$id'";
        $checkResult = $this->conn->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $this->errors[] = "Row $rowNum: Student ID '$id' already exists (skipped)";
            $this->skipped++;
            return;
        }
        
        // Insert query
        $insertQuery = "INSERT INTO students 
            (StudentID, StudentFName, StudentMName, StudentLName, Gender, BirthDate, Course, YearLvl, Section, Department, isActive, IsEnroll)
            VALUES 
            ('$id', '$fname', '$mname', '$lname', '$gender', '$birthdate', '$course', $yearlvl, '$section', '$dept', 1, 1)";
        
        if ($this->conn->query($insertQuery) === TRUE) {
            $this->imported++;
        } else {
            $this->errors[] = "Row $rowNum (ID: $id): " . $this->conn->error;
            $this->skipped++;
        }
    }
    
    private function printSummary($previewOnly = false) {
        echo "\n" . str_repeat("=", 100) . "\n";
        echo ($previewOnly ? "PREVIEW ANALYSIS SUMMARY" : "IMPORT SUMMARY") . "\n";
        echo str_repeat("=", 100) . "\n";
        echo "Records Processed: $this->imported\n";
        echo "Records Skipped: $this->skipped\n";
        echo "Total: " . ($this->imported + $this->skipped) . "\n";
        
        if (!empty($this->errors)) {
            echo "\nErrors Found: " . count($this->errors) . "\n";
            echo "\n" . str_repeat("-", 100) . "\n";
            echo "ERROR DETAILS (showing first 20):\n";
            echo str_repeat("-", 100) . "\n";
            for ($i = 0; $i < min(20, count($this->errors)); $i++) {
                echo ($i + 1) . ". " . $this->errors[$i] . "\n";
            }
            if (count($this->errors) > 20) {
                echo "\n... and " . (count($this->errors) - 20) . " more errors\n";
            }
        } else {
            echo "\nNo errors found!\n";
        }
        
        if ($previewOnly) {
            echo "\n" . str_repeat("-", 100) . "\n";
            echo "TO PROCEED WITH IMPORT, run: php import_students.php import\n";
            echo str_repeat("-", 100) . "\n";
        }
    }
}

// Get command line argument
$mode = $argv[1] ?? 'preview';

if (!in_array($mode, ['preview', 'import'])) {
    echo "Usage: php import_students.php [preview|import]\n";
    echo "  preview - Analyze and show what will be imported (no database changes)\n";
    echo "  import  - Actually import the data into the database\n";
    exit(1);
}

// Initialize database connection
$conn = null;
if ($mode === 'import') {
    $conn = new mysqli('localhost', 'root', '', 'ctu_scanner');
    if ($conn->connect_error) {
        die("Database Connection failed: " . $conn->connect_error);
    }
}

// Run importer
$importer = new StudentImporter($conn);

if ($mode === 'preview') {
    $importer->preview('sql/students82725.xlsx');
} else {
    echo "WARNING: This will insert records into the database!\n";
    echo "Continue? (yes/no): ";
    $input = trim(fgets(STDIN));
    if (strtolower($input) === 'yes') {
        $importer->import('sql/students82725.xlsx');
    } else {
        echo "Import cancelled.\n";
    }
}

if ($conn) {
    $conn->close();
}
?>
