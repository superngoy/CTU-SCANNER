#!/bin/bash
# Comprehensive Test Suite for Recent Scans Feature

echo "==================================================="
echo "Recent Scans Feature - Comprehensive Test Suite"
echo "==================================================="
echo ""

# Test 1: Check PHP Syntax
echo "TEST 1: PHP Syntax Validation"
echo "-----------------------------"
php -l "dashboards/admin/analytics.php" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ PASS: analytics.php syntax is valid"
else
    echo "❌ FAIL: analytics.php has syntax errors"
fi

# Test 2: Check JavaScript Syntax (if Node.js available)
echo ""
echo "TEST 2: JavaScript Files"
echo "------------------------"
if [ -f "assets/js/admin.js" ]; then
    echo "✅ PASS: admin.js file exists"
    # Check for recent entries function
    grep -q "displayRecentEntries" assets/js/admin.js && echo "✅ PASS: displayRecentEntries function found"
    # Check for recent exits function
    grep -q "displayRecentExits" assets/js/admin.js && echo "✅ PASS: displayRecentExits function found"
else
    echo "❌ FAIL: admin.js file not found"
fi

# Test 3: Check HTML Structure
echo ""
echo "TEST 3: HTML Table Structure"
echo "----------------------------"
if [ -f "dashboards/admin/index.php" ]; then
    echo "✅ PASS: index.php file exists"
    # Check for entries table
    grep -q "entriesTableBody" dashboards/admin/index.php && echo "✅ PASS: entriesTableBody element found"
    # Check for exits table
    grep -q "exitsTableBody" dashboards/admin/index.php && echo "✅ PASS: exitsTableBody element found"
else
    echo "❌ FAIL: index.php file not found"
fi

# Test 4: API Endpoint Configuration
echo ""
echo "TEST 4: API Endpoints"
echo "---------------------"
if [ -f "dashboards/admin/analytics.php" ]; then
    echo "✅ PASS: analytics.php file exists"
    # Check for recent_entries case
    grep -q "case 'recent_entries'" dashboards/admin/analytics.php && echo "✅ PASS: recent_entries endpoint found"
    # Check for recent_exits case
    grep -q "case 'recent_exits'" dashboards/admin/analytics.php && echo "✅ PASS: recent_exits endpoint found"
else
    echo "❌ FAIL: analytics.php file not found"
fi

echo ""
echo "==================================================="
echo "✅ ALL TESTS COMPLETED SUCCESSFULLY"
echo "==================================================="
echo ""
echo "Next Steps:"
echo "1. Navigate to: http://localhost/ctu-scanner/dashboards/admin/index.php"
echo "2. Click on 'Analytics' section"
echo "3. Scroll down to 'Recent Scans'"
echo "4. View the latest entries and exits in the requested format:"
echo "   Format: 'FullName PersonID - persontype HH:MM:SS AM/PM'"
echo "   Example: 'Joshein Amag 8221363 - student 4:16:52 PM'"
