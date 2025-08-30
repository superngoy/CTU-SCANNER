<?php
require_once '../../includes/functions.php';

$scanner = new CTUScanner();
$startDate = $_GET['start_date'] ?? date('Y-m-d');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Simple PDF generation using HTML/CSS
header('Content-Type: application/pdf');
header('Content-Disposition: attachment;filename="CTU_Scanner_Report_' . $startDate . '_to_' . $endDate . '.pdf"');

// For a proper PDF, you would use a library like TCPDF or FPDF
// This is a simplified version that outputs HTML that can be printed as PDF

echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 20px; }';
echo 'table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }';
echo 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
echo 'th { background-color: #4CAF50; color: white; }';
echo '.header { text-align: center; margin-bottom: 30px; }';
echo '.section-title { background-color: #2196F3; color: white; font-weight: bold; }';
echo '@media print { body { margin: 0; } }';
echo '</style>';
echo '</head>';
echo '<body>';

echo '<div class="header">';
echo '<h1>CTU Scanner System Report</h1>';
echo '<p>Period: ' . $startDate . ' to ' . $endDate . '</p>';
echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
echo '</div>';

// Summary Statistics
$stats = $scanner->getDailyStats($startDate);
echo '<h2>Summary Statistics</h2>';
echo '<table>';
echo '<tr><th>Metric</th><th>Count</th></tr>';
echo '<tr><td>Total Entries</td><td>' . $stats['total_entries'] . '</td></tr>';
echo '<tr><td>Total Exits</td><td>' . $stats['total_exits'] . '</td></tr>';
echo '<tr><td>Student Entries</td><td>' . $stats['student_entries'] . '</td></tr>';
echo '<tr><td>Faculty Entries</td><td>' . $stats['faculty_entries'] . '</td></tr>';
echo '</table>';

echo '</body>';
echo '</html>';

// JavaScript to trigger print dialog
echo '<script>window.print();</script>';
?>