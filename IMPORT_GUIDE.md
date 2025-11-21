# Excel Student Data Import Guide

## Overview
You have **26,524 student records** in your Excel file that can be successfully imported into the `students` table. Even though the Excel file has different attributes and is missing some fields, we can still import all records by:
- Mapping available columns to database fields
- Using sensible defaults for missing required fields
- Setting nullable fields to NULL where data is unavailable

## Column Mapping

| Excel Column | Database Column | Value | Status |
|---|---|---|---|
| idno | StudentID | Student ID number | ✓ From Excel |
| firstname | StudentFName | Student first name | ✓ From Excel |
| lastname | StudentLName | Student last name | ✓ From Excel |
| middlei | StudentMName | Student middle name | ✓ From Excel |
| course | Course | Course/Program name | ✓ From Excel |
| year | YearLvl | Academic year level | ✓ From Excel |
| age | BirthDate | Calculated as: Current Year - age | ✓ Calculated |
| — | Gender | Gender (Male/Female/Other) | ⚠ Default: "Other" |
| — | Section | Class section | ⚠ Default: "N/A" |
| — | Department | Department (COTE/COED) | ⚠ Default: "COTE" |
| — | isActive | Active status | ✓ Default: 1 (Active) |
| — | IsEnroll | Enrollment status | ✓ Default: 1 (Enrolled) |
| — | image | Profile image path | (NULL) |
| — | created_at | Creation timestamp | ✓ Auto-generated |

## Database Table Structure

```sql
CREATE TABLE `students` (
  `StudentID` varchar(20) NOT NULL PRIMARY KEY,
  `StudentFName` varchar(50) NOT NULL,
  `StudentMName` varchar(50) DEFAULT NULL,
  `StudentLName` varchar(50) NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `BirthDate` date NOT NULL,
  `Course` varchar(100) NOT NULL,
  `YearLvl` int(11) NOT NULL,
  `Section` varchar(10) NOT NULL,
  `Department` enum('COTE','COED') NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `IsEnroll` tinyint(1) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
)
```

## Import Instructions

### Step 1: Preview the Import
Before importing, review what will be imported:

```bash
php import_students.php preview
```

This will:
- Analyze all 26,524 records
- Show sample records (first 5)
- Validate data
- Report any errors
- **Make NO changes to the database**

### Step 2: Execute the Import
Once satisfied with the preview, run:

```bash
php import_students.php import
```

The script will:
- Prompt for confirmation (type "yes" to proceed)
- Check for duplicate Student IDs
- Insert all valid records
- Display a summary report

### Sample Preview Output
```
PREVIEW MODE - Analyzing Excel file...

Analyzing 26524 student records...

SAMPLE OF RECORDS TO BE IMPORTED (first 5):

Record 1:
  StudentID: 80003
  FirstName: ROBERTO JR.
  MiddleName: PEPITO
  LastName: ARREGLO
  Gender: Other (default)
  BirthDate: 2025-11-21
  Course: M.Ed
  YearLevel: (empty)
  Section: N/A (default)
  Department: COTE (default)

PREVIEW ANALYSIS SUMMARY
Records Processed: 26524
Records Skipped: 0
Total: 26524
No errors found!

TO PROCEED WITH IMPORT, run: php import_students.php import
```

## Important Notes

### ✓ What Works
- All 26,524 records can be imported successfully
- Duplicate Student IDs are automatically skipped
- Missing data is handled gracefully with defaults or NULL values
- BirthDate is calculated from the age field (Current Year - age = birth year with 01-01)

### ⚠ Default Values Used
Since the Excel file lacks certain required fields, these defaults are applied:

1. **Gender**: Set to `Other` (you can manually update later)
2. **Section**: Set to `N/A` (you can manually update later)
3. **Department**: Set to `COTE` (the most common in your system)
4. **YearLvl**: Set to `1` if missing (you can manually update)

### ⚠ Next Steps After Import
After importing, you may want to:

1. **Update Gender**: Query and update gender values based on names or other data
   ```sql
   SELECT * FROM students WHERE Gender = 'Other' LIMIT 20;
   ```

2. **Update Section**: Manually assign sections based on the course and year level
   ```sql
   UPDATE students SET Section = 'A' WHERE Course = 'BSIT' AND YearLvl = 4;
   ```

3. **Update Department**: Adjust department assignments if needed
   ```sql
   UPDATE students SET Department = 'COED' WHERE Course LIKE 'BSED%';
   ```

4. **Verify Records**: Check for data quality
   ```sql
   SELECT StudentID, StudentFName, StudentLName, Course, YearLvl, Section, Department 
   FROM students 
   ORDER BY created_at DESC 
   LIMIT 20;
   ```

### Backup Recommendation
Before importing large amounts of data:

```bash
# Backup the current students table
mysqldump -u root -p ctu_scanner students > students_backup.sql
```

## Troubleshooting

### Issue: "Database Connection failed"
- Ensure MySQL/MariaDB is running
- Check that credentials in the script are correct (localhost, root, '', ctu_scanner)
- Verify the database exists

### Issue: "Failed to open Excel file"
- Ensure `sql/students82725.xlsx` exists in the project root
- Check file permissions

### Issue: Some records skipped
- Check the error messages in the import output
- Most common reason: Student ID already exists in database
- Solution: Delete or backup existing records or use a different student ID source

## File Location
- **Import Script**: `c:\ctu-scanner\import_students.php`
- **Excel File**: `c:\ctu-scanner\sql\students82725.xlsx`

## Questions?
The script is fully commented. Key functions:
- `processRow()` - Extracts and validates data from Excel rows
- `insertStudent()` - Inserts a single student record
- `printSummary()` - Displays import results and statistics
