<?php
/**
 * Security Improvements Test & Verification
 * Tests the XSS protections implemented in the system
 */

require_once __DIR__ . '/../config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Security Implementation Verification</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; }
        .test-item { margin: 15px 0; padding: 15px; border-left: 4px solid #972529; background: #f8f9fa; }
        .pass { border-left-color: #27AE60; background: #f0fdf4; }
        .fail { border-left-color: #dc3545; background: #fef2f2; }
        code { background: #f5f5f5; padding: 5px 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>CTU Scanner - Security Implementation Verification</h1>
";

$tests = [];

// Test 1: Sanitize Functions Loaded
$test1 = function_exists('escapeOutput') && function_exists('sanitizeInput');
$tests[] = [
    'name' => 'Sanitize Functions Available',
    'pass' => $test1,
    'description' => 'Verify sanitize.php functions are loaded',
    'expected' => 'escapeOutput() and sanitizeInput() functions exist',
    'actual' => $test1 ? 'Available' : 'Not found'
];

// Test 2: Test escapeOutput
$xss_payload = "<img src=x onerror=\"alert('XSS')\">";
$escaped = escapeOutput($xss_payload, 'html');
$test2 = $escaped !== $xss_payload && strpos($escaped, '&lt;') !== false;
$tests[] = [
    'name' => 'HTML Escaping Works',
    'pass' => $test2,
    'description' => 'XSS payload should be escaped',
    'expected' => 'HTML tags converted to entities',
    'actual' => $test2 ? 'Properly escaped: ' . htmlspecialchars($escaped) : 'Not escaped'
];

// Test 3: Test email validation
$test3a = validateEmail('test@example.com');
$test3b = !validateEmail('not-an-email');
$test3 = $test3a && $test3b;
$tests[] = [
    'name' => 'Email Validation Works',
    'pass' => $test3,
    'description' => 'Valid emails accepted, invalid rejected',
    'expected' => 'Valid email passes, invalid fails',
    'actual' => $test3 ? 'Both tests passed' : 'Validation failed'
];

// Test 4: Test phone validation
$test4a = validatePhoneNumber('09123456789');
$test4b = validatePhoneNumber('+63-912-345-6789');
$test4c = !validatePhoneNumber('abc');
$test4 = $test4a && $test4b && $test4c;
$tests[] = [
    'name' => 'Phone Validation Works',
    'pass' => $test4,
    'description' => 'Valid phone formats accepted, invalid rejected',
    'expected' => 'Multiple formats accepted, invalid rejected',
    'actual' => $test4 ? 'All phone validation tests passed' : 'Phone validation failed'
];

// Test 5: Test filename sanitization
$dangerous_filename = '../../etc/passwd.txt';
$safe_filename = sanitizeFilename($dangerous_filename);
$test5 = $safe_filename !== $dangerous_filename && strpos($safe_filename, '../') === false;
$tests[] = [
    'name' => 'Filename Sanitization Works',
    'pass' => $test5,
    'description' => 'Path traversal attempts blocked in filenames',
    'expected' => 'Path traversal sequences removed',
    'actual' => $test5 ? 'Safely converted to: ' . $safe_filename : 'Not sanitized'
];

// Test 6: Security headers sent
$headers_list = headers_list();
$has_xss_header = in_array('X-XSS-Protection: 1; mode=block', $headers_list);
$has_frame_header = in_array('X-Frame-Options: SAMEORIGIN', $headers_list);
$has_content_header = in_array('X-Content-Type-Options: nosniff', $headers_list);
$test6 = $has_xss_header && $has_frame_header && $has_content_header;
$tests[] = [
    'name' => 'Security Headers Sent',
    'pass' => $test6,
    'description' => 'XSS and other protective headers configured',
    'expected' => 'X-XSS-Protection, X-Frame-Options, X-Content-Type-Options headers present',
    'actual' => $test6 ? 'All headers present' : 'Some headers missing: ' . json_encode(['xss' => $has_xss_header, 'frame' => $has_frame_header, 'content' => $has_content_header])
];

// Test 7: CSP header present
$has_csp = false;
foreach ($headers_list as $header) {
    if (strpos($header, 'Content-Security-Policy') !== false) {
        $has_csp = true;
        break;
    }
}
$tests[] = [
    'name' => 'Content-Security-Policy Configured',
    'pass' => $has_csp,
    'description' => 'CSP header restricts script sources',
    'expected' => 'Content-Security-Policy header configured',
    'actual' => $has_csp ? 'CSP header present' : 'CSP header missing'
];

// Test 8: Form validation
$rules = [
    'email' => ['type' => 'email', 'required' => true],
    'phone' => ['type' => 'phone', 'required' => true, 'min_length' => 7],
];
$data = [
    'email' => 'test@example.com',
    'phone' => '09123456789'
];
$validation = validateFormData($data, $rules);
$test8 = $validation['valid'];
$tests[] = [
    'name' => 'Form Validation System Works',
    'pass' => $test8,
    'description' => 'Comprehensive form validation with rules',
    'expected' => 'Valid form data passes validation',
    'actual' => $test8 ? 'Validation passed' : 'Errors: ' . json_encode($validation['errors'])
];

// Test 9: Sanitize input function
$raw_input = "  <script>alert('xss')</script>  ";
$sanitized = sanitizeInput($raw_input);
$test9 = $sanitized !== $raw_input && strpos($sanitized, '<') === false && strpos($sanitized, '>') === false;
$tests[] = [
    'name' => 'Input Sanitization Removes Tags',
    'pass' => $test9,
    'description' => 'Script tags and HTML are stripped from input',
    'expected' => 'Script tags removed, whitespace trimmed',
    'actual' => $test9 ? 'Sanitized to: ' . htmlspecialchars($sanitized) : 'Sanitization failed'
];

// Display results
$pass_count = 0;
foreach ($tests as $test) {
    if ($test['pass']) $pass_count++;
    $class = $test['pass'] ? 'pass' : 'fail';
    $status = $test['pass'] ? '✅ PASS' : '❌ FAIL';
    
    echo "
        <div class='test-item $class'>
            <h5>$status - {$test['name']}</h5>
            <p class='mb-1'><strong>Test:</strong> {$test['description']}</p>
            <p class='mb-1'><strong>Expected:</strong> {$test['expected']}</p>
            <p class='mb-0'><strong>Result:</strong> {$test['actual']}</p>
        </div>
    ";
}

$total = count($tests);
$percentage = ($pass_count / $total) * 100;

echo "
    <div class='mt-5 p-3 border rounded' style='background: #f8f9fa; border: 2px solid #972529;'>
        <h3>Summary</h3>
        <p class='mb-0'><strong>Tests Passed:</strong> $pass_count / $total ({$percentage}%)</p>
";

if ($pass_count === $total) {
    echo "
        <div class='alert alert-success mt-3' role='alert'>
            <i class='fas fa-check-circle'></i>
            <strong>✅ All Security Tests Passed!</strong>
            Your CTU Scanner system has all XSS protections in place.
        </div>
    ";
} else {
    echo "
        <div class='alert alert-warning mt-3' role='alert'>
            <i class='fas fa-exclamation-triangle'></i>
            <strong>⚠️ Some tests failed.</strong>
            Please review the failed tests above.
        </div>
    ";
}

echo "
        <hr>
        <h4>Next Steps</h4>
        <ol>
            <li>Review the implementation in modified files</li>
            <li>Test all dashboard functionality</li>
            <li>Check that forms still work correctly</li>
            <li>Verify table displays and updates work</li>
            <li>Test file uploads still function</li>
            <li>Review SECURITY_UPDATES_README.md for documentation</li>
        </ol>
    </div>

    <div class='mt-5 p-3' style='background: #f0f9ff; border-left: 4px solid #0084ff; border-radius: 4px;'>
        <h4>Important Notes</h4>
        <ul>
            <li><strong>Backward Compatible:</strong> All existing functionality should work as before</li>
            <li><strong>Database:</strong> Already uses prepared statements (secure)</li>
            <li><strong>Performance:</strong> Minimal impact from security improvements</li>
            <li><strong>DOMPurify:</strong> Now loaded in admin/security/scanner dashboards</li>
            <li><strong>Validation:</strong> All form inputs validated and sanitized</li>
        </ul>
    </div>
    </div>
</body>
</html>
";
?>
