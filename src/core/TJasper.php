<?php

namespace JasperPHP\core;

use JasperPHP\elements\Report;
use JasperPHP\core\Instructions;
use JasperPHP\processors\XlsProcessor;
use JasperPHP\processors\XlsxProcessor;
use JasperPHP\processors\HtmlProcessor;

/**
 * TJasper class
 * This class serves as the main entry point for generating Jasper reports.
 */
class TJasper {

    private $report;
    private $type;

    /**
     * method __construct()
     * 
     * @param $jrxml = a jrxml file name
     * @param $param = a array with params to use into jrxml report
     */
    public function __construct($jrxml, array $param, array $dataSourceConfig, $debugMode = false) {
        $GLOBALS['reports'] = array();
        $xmlFile = $jrxml;
        $this->type = (array_key_exists('type', $param)) ? $param['type'] : 'pdf';
        $this->report = new Report($xmlFile, $param, null, $debugMode, $dataSourceConfig); // $GLOBALS['reports'][$xmlFile];
        switch ($this->type) {
            case 'pdf':
                Instructions::prepare($this->report);
                break;
            case 'xls':
                Instructions::setProcessor(XlsProcessor::class);
                Instructions::prepare($this->report);
                break;
            case 'xlsx':                //Process use 'PHPOffice/PhpSpreadsheet'
                Instructions::setProcessor(XlsxProcessor::class);
                Instructions::prepare($this->report);
                break;            case 'html':
                Instructions::setProcessor(HtmlProcessor::class);
                Instructions::prepare($this->report);
                break;
        }
    }

    /**
     * Generates the report and outputs it based on the specified mode.
     *
     * @param string $outputMode 'I' for inline, 'D' for download, 'F' for file, 'S' for string.
     * @param string $filename The filename for download or file output.
     * @param string $filePath The path where the file will be saved if $outputMode is 'F'.
     * @return string|void Returns the report content as a string if $outputMode is 'S', otherwise void.
     */
    public function output($outputMode = 'I', $filename = 'report.pdf', $filePath = null) {
        $this->report->generate();
        $this->report->out(); // This prepares the output object (e.g., FPDF, HTML content)

        switch ($this->type) {
            case 'pdf':
                $pdf = Instructions::get();
                if ($outputMode === 'F' && $filePath !== null) {
                    return $pdf->Output($filePath, $outputMode);
                } else {
                    return $pdf->Output($filename, $outputMode);
                }
                break;
            case 'xls':
            case 'xlsx':
                // For XLS/XLSX, the output is typically handled by the processor directly writing to php://output
                // or saving to a file. The current Instructions::get() might return the Spreadsheet object.
                // This part needs to be adapted based on how XlsProcessor/XlsxProcessor handle output.
                // Assuming Instructions::get() returns the Spreadsheet object for now.
                $spreadsheet = Instructions::get();
                $writer = null;
                if ($this->type === 'xls') {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                } else { // xlsx
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                }

                if ($outputMode === 'S') {
                    ob_start();
                    $writer->save('php://output');
                    return ob_get_clean();
                } elseif ($outputMode === 'F' && $filePath !== null) {
                    $writer->save($filePath);
                    return ''; // No direct content to return for file save
                } else { // 'I' or 'D'
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: ' . ($outputMode === 'D' ? 'attachment' : 'inline') . ';filename="' . $filename . '"');
                    header('Cache-Control: max-age=0');
                    $writer->save('php://output');
                    return ''; // No direct content to return for browser output
                }
                break;
            case 'html':
                $html = Instructions::get();
                if ($outputMode === 'S') {
                    return $html->getHtmlContent(); // Assuming getHtmlContent() exists or similar
                } elseif ($outputMode === 'F' && $filePath !== null) {
                    file_put_contents($filePath, $html->getHtmlContent());
                    return '';
                } else { // 'I' or 'D' (inline for HTML is typical)
                    $html->out();
                    return '';
                }
                break;
            default:
                // Handle unsupported types or throw an exception
                throw new \Exception("Unsupported report type for output: " . $this->type);
        }
    }

    public function setVariable($name, $value) {
        $this->report->arrayVariable[$name]['initialValue'] = $value;
    }

    public function getReport() {
        return $this->report;
    }

}
