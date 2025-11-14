<?php

use JasperPHP\core\TJasper as NewTJasper;
use JasperPHP\database\TConnection;
use JasperPHP\database\TTransaction;
use JasperPHP\database\TLoggerHTML;

/**
 * classe TJasper - Compatibility Layer
 *
 * This class is a compatibility wrapper to allow old code to work with the new TJasper class structure.
 * It mimics the old class's public API and translates calls to the new JasperPHP\core\TJasper class.
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br> (Original)
 * @author   Gemini Assistant (Compatibility Layer)
 * @version  2023.11.13
 * @access   restrict
 */
class TJasper
{
    /**
     * @var NewTJasper
     */
    private $jasper;
    private $type;
    private $param;

    /**
     * method __construct()
     *
     * @param string $jrxml a jrxml file name
     * @param array $param a array with params to use into jrxml report
     */
    public function __construct($jrxml, array $param)
    {
        $this->param = $param;
        $this->type = (array_key_exists('type', $param)) ? $param['type'] : 'pdf';

        // Determine debug mode from parameters
        $debugMode = isset($param['debug']) && $param['debug'] === 'true';

        // Default to array datasource if no specific data source is configured
        $dataSourceConfig = ['type' => 'array', 'data' => []];

        // If a database connection is open, prepare the db datasource config.
        // This maintains compatibility with code that uses TTransaction::open().
        if (TTransaction::get()) {
            // This is a bit of a hack. TTransaction doesn't expose its config name.
            // We assume the 'dev' config if a connection exists.
            // A better long-term solution would be to refactor how DB config is passed.
            $dbinfo = TConnection::getDatabaseInfo('dev'); // Assumes 'dev.ini'
            $dataSourceConfig = [
                'type' => 'db',
                'db_driver' => $dbinfo['type'] ?? 'mysql',
                'db_host' => $dbinfo['host'] ?? '127.0.0.1',
                'db_port' => $dbinfo['port'] ?? '3306',
                'db_name' => $dbinfo['name'] ?? '',
                'db_user' => $dbinfo['user'] ?? '',
                'db_pass' => $dbinfo['pass'] ?? '',
            ];
        }
        
        // The new TJasper expects the full path to the jrxml file.
        // The old class assumed it was in a specific folder. We'll replicate that assumption.
        $report_path = __DIR__ . '/../app.jrxml/' . basename($jrxml);
        if (!file_exists($report_path)) {
             $report_path = $jrxml; // Fallback to the provided path if not found in the default folder.
        }

        // Instantiate the new TJasper class
        $this->jasper = new NewTJasper($report_path, $this->param, $dataSourceConfig, $debugMode);
    }

    /**
     * outpage
     *
     * Generates and outputs the report to the browser.
     * This method maps the old outpage call to the new output() method.
     *
     * @param string $type Deprecated. The type is now set in the constructor.
     */
    public function outpage($type = 'pdf')
    {
        // The type is now primarily controlled by the 'type' parameter in the constructor.
        // We use $this->type which was set during construction.
        $outputMode = 'I'; // Inline display
        $filename = 'report.' . $this->type;

        // The new output method handles sending headers and content.
        $this->jasper->output($outputMode, $filename);
    }

    /**
     * setVariable
     *
     * Sets a variable in the report.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setVariable($name, $value)
    {
        $this->jasper->setVariable($name, $value);
    }
}

// The following block is the original usage example from the old file.
// It should now work with the compatibility layer above.
// Note: The autoloader from composer should handle class loading.
// Explicit require statements are generally not needed if using composer.

// require('autoloader.php'); // This is likely replaced by composer's autoload
// require('../../tecnickcom/tcpdf/tcpdf.php'); // Should be loaded by composer
// require('../../phpoffice/phpexcel/Classes/PHPExcel.php'); // Should be loaded by composer

// Check if vendor autoload exists
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// The example usage from the old file
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $report_name = isset($_GET['report']) ? $_GET['report'] : 'testReport.jrxml';  // sql into testReport.txt report do not select any table.
        
        // Open transaction with the 'dev' configuration
        TTransaction::open('dev');
        TTransaction::setLogger(new TLoggerHTML('log.html'));

        // This setting is handled inside the new Report class logic, but we leave it here for context.
        // JasperPHP\elements\Report::$proccessintructionsTime = 'inline';

        // The $_GET parameters are passed to the constructor
        $jasper = new TJasper($report_name, $_GET);
        $jasper->outpage();

        // Close the transaction
        TTransaction::close();

    } catch (Exception $e) {
        // Error handling
        echo 'Error: ' . $e->getMessage();
        TTransaction::rollback();
    }
}