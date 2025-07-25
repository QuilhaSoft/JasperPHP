<?php

require_once __DIR__ . '/../vendor/autoload.php';

use JasperPHP\core\TJasper;
use JasperPHP\database\TTransaction;
use JasperPHP\database\TLoggerHTML;

// This is a simplified example. In a real application, you would want more robust error handling and input validation.

$report_name = isset($_GET['report']) ? __DIR__ . '/../app.jrxml/' . $_GET['report'] : __DIR__ . '/../app.jrxml/testReport.jrxml';
$report_type = isset($_GET['type']) ? $_GET['type'] : 'pdf';
$debugMode = isset($_GET['debug']) && $_GET['debug'] === 'true';

$dataSourceConfig = [
    'type' => 'array', // Default type
    'data' => [ // Default sample data for 'array' type
        (object)['id' => 1, 'name' => 'Item A', 'quantity' => 10],
        (object)['id' => 2, 'name' => 'Item B', 'quantity' => 20],
        (object)['id' => 3, 'name' => 'Item C', 'quantity' => 30],
    ],
];

if (isset($_GET['data_source'])) {
    $dataSourceType = $_GET['data_source'];
    switch ($dataSourceType) {
        case 'db':
            if (isset($_GET['sql'])) {
                $dataSourceConfig = [
                    'type' => 'db',
                    'sql' => $_GET['sql'],
                    'db_driver' => $_GET['db_driver'] ?? 'mysql',
                    'db_host' => $_GET['db_host'] ?? 'localhost',
                    'db_port' => $_GET['db_port'] ?? '3306',
                    'db_name' => $_GET['db_name'] ?? '',
                    'db_user' => $_GET['db_user'] ?? '',
                    'db_pass' => $_GET['db_pass'] ?? '',
                ];
            } else {
                // Fallback to default array data if SQL is missing for DB source
                // Add debug message later in Report class
            }
            break;
        case 'json_file':
            if (isset($_GET['path'])) {
                $dataSourceConfig = [
                    'type' => 'json_file',
                    'path' => $_GET['path'],
                ];
            } else {
                // Fallback to default array data if path is missing for JSON file source
                // Add debug message later in Report class
            }
            break;
        case 'csv_file':
            if (isset($_GET['path'])) {
                $dataSourceConfig = [
                    'type' => 'csv_file',
                    'path' => $_GET['path'],
                ];
            } else {
                // Fallback to default array data if path is missing for CSV file source
                // Add debug message later in Report class
            }
            break;
        case 'array':
            // If data_source=array is explicitly set, but no 'data' param, use default sample data.
            // Could also allow passing JSON string in 'data' param for array, but keeping it simple for now.
            break;
        default:
            // Unknown data source type, fall back to default array data
            // Add debug message later in Report class
            break;
    }
}

try {
    // Pass dataSourceConfig instead of sampleData
    $jasper = new TJasper($report_name, ['type' => $report_type], $dataSourceConfig, $debugMode);

    if ($debugMode) {
        // In debug mode, execute generate() to collect messages, but don't output PDF
        $jasper->getReport()->generate();

        echo "<pre>\n";
        echo "DEBUG MESSAGES:\n";
        echo "-------------------\n";
        foreach ($jasper->getReport()->debugMessages as $message) {
            echo htmlspecialchars($message) . "\n";
        }
        echo "</pre>";
    } else {
        // In normal mode, output the report
        $jasper->outpage($report_type);
    }
} catch (\Exception $e) {

    // Basic error handling for demonstration purposes
    echo "Error generating report: " . $e->getMessage();
    // Log the error for debugging
    TTransaction::log("Error generating report: " . $e->getMessage());
}


