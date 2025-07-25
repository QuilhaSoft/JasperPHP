<?php

namespace JasperPHP\processors;

use JasperPHP\elements\Report;

class HtmlProcessor
{
    private $output;

    public function __construct()
    {
        
        // Initialize HTML specific settings if any
    }

    public function process(Report $report)
    {
        // This is a placeholder. Actual HTML generation logic will go here.
        // For now, it will just return a simple HTML string.
        $html = '<html><head><title>JasperPHP HTML Report</title></head><body>';
        $html .= '<h1>Report: ' . $report->name . '</h1>';
        $html .= '<p>This is a basic HTML representation of your report.</p>';
        
        // Example of iterating through data if available
        if (!empty($report->dbData)) {
            $html .= '<h2>Data:</h2><ul>';
            foreach ($report->dbData as $row) {
                $html .= '<li>';
                foreach ($row as $key => $value) {
                    $html .= $key . ': ' . $value . ' ';
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</body></html>';
        $this->output = $html;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function out()
    {
        header('Content-Type: text/html');
        echo $this->output;
    }
}