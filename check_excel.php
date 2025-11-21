<?php
$excelFile = 'sql/students82725.xlsx';
$zip = new ZipArchive();

if ($zip->open($excelFile) === TRUE) {
    // Get shared strings (which contain text values)
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    $stringsXml = simplexml_load_string($sharedStringsXml);
    
    // Extract all text strings
    $strings = [];
    foreach ($stringsXml->si as $si) {
        $strings[] = (string)$si->t;
    }
    
    // Get worksheet data
    $xmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
    $xml = simplexml_load_string($xmlContent);
    $zip->close();
    
    echo "Excel File Analysis\n";
    echo "===================\n\n";
    
    // Parse first row to get headers
    echo "Headers:\n";
    $headers = [];
    $firstRow = $xml->sheetData->row[0];
    $colCount = 0;
    foreach ($firstRow->c as $cell) {
        $cellRef = (string)$cell['r']; // e.g., "A1", "B1"
        $cellValue = (string)$cell->v;
        
        // If cell has type "s", the value is an index into shared strings
        $cellType = (string)$cell['t'];
        if ($cellType === 's') {
            $value = isset($strings[(int)$cellValue]) ? $strings[(int)$cellValue] : "String #$cellValue";
        } else {
            $value = $cellValue;
        }
        
        $headers[] = $value;
        echo "Col " . (count($headers)) . ": $value\n";
        $colCount++;
    }
    
    echo "\nTotal Columns: " . $colCount . "\n";
    echo "Total Rows: " . count($xml->sheetData->row) . "\n";
    
    echo "\n\nFirst 3 data rows:\n";
    echo str_repeat("-", 120) . "\n";
    
    // Show first few data rows
    for ($r = 1; $r < min(4, count($xml->sheetData->row)); $r++) {
        $row = $xml->sheetData->row[$r];
        foreach ($row->c as $cell) {
            $cellValue = (string)$cell->v;
            $cellType = (string)$cell['t'];
            
            if ($cellType === 's') {
                $value = isset($strings[(int)$cellValue]) ? $strings[(int)$cellValue] : "";
            } else {
                $value = $cellValue;
            }
            
            echo str_pad(substr($value, 0, 15), 15) . " | ";
        }
        echo "\n";
    }
    
} else {
    echo "Failed to open Excel file\n";
}
?>
