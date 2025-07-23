<?php

require_once __DIR__ . '/../vendor/autoload.php';

use JasperPHP\TJasper;
use JasperPHP\ado\TTransaction;
use JasperPHP\ado\TLoggerHTML;

// This is a simplified example. In a real application, you would want more robust error handling and input validation.

$report_name = isset($_GET['report']) ? __DIR__ . '/../app.jrxml/' . $_GET['report'] : __DIR__ . '/../app.jrxml/testReport.jrxml';
$report_type = isset($_GET['type']) ? $_GET['type'] : 'pdf'; // Can be 'pdf', 'xls', 'xlsx', or 'html'



try {
    $sampleData = [
        (object)['id' => 1, 'name' => 'Item A', 'quantity' => 10],
        (object)['id' => 2, 'name' => 'Item B', 'quantity' => 20],
        (object)['id' => 3, 'name' => 'Item C', 'quantity' => 30],
    ];

    $jasper = new TJasper($report_name, ['type' => $report_type], $sampleData);
    $jasper->outpage($report_type);
} catch (\Exception $e) {
    // Basic error handling for demonstration purposes
    echo "Error generating report: " . $e->getMessage();
    // Log the error for debugging
    TTransaction::log("Error generating report: " . $e->getMessage());
}


