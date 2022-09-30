<?php

use JasperPHP\Report;
use JasperPHP\ado\TTransaction;
use JasperPHP\ado\TLogger;
use JasperPHP\ado\TLoggerHTML;

//use \NumberFormatter;
//use PHPexcel as PHPexcel; // experimental
/**
 * classe TJasper
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2018.10.15
 * @access   restrict
 * 
 * 2015.03.11 -- create
 * 2018.10.15 -- revision and internationalize, add TLogger classes
 * */
class TJasper {

    private $report;
    private $type;
    private $param;

    /**
     * method __construct()
     * 
     * @param $jrxml = a jrxml file name
     * @param $param = a array with params to use into jrxml report
     */
    public function __construct($jrxml, array $param) {
        $GLOBALS['reports'] = array();
        $xmlFile = $jrxml;
        $this->type = (array_key_exists('type', $param)) ? $param['type'] : 'pdf';
        error_reporting(0);
        $this->param = $param;
        $this->report = new JasperPHP\Report($xmlFile, $param); // $GLOBALS['reports'][$xmlFile];
        switch ($this->type) {
            case 'pdf':
                JasperPHP\Instructions::prepare($this->report);
                break;
            case 'xls':
                JasperPHP\Instructions::setProcessor('\JasperPHP\XlsProcessor');
                JasperPHP\Instructions::prepare($this->report);
                break;
        }
    }

    public function outpage($type = 'pdf') {
        $this->report->generate();
        $this->report->out();
        switch ($this->type) {
            case 'pdf':
                $pdf = JasperPHP\Instructions::get();
                $pdf->Output('report.pdf', "I");
                break;
            case 'xls':
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="01simple.xls"');
                header('Cache-Control: max-age=0');
                // If you're serving to IE 9, then the following may be needed
                header('Cache-Control: max-age=1');
                // If you're serving to IE over SSL, then the following may be needed
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
                header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header('Pragma: public'); // HTTP/1.0
                $objWriter = PHPExcel_IOFactory::createWriter(JasperPHP\Instructions::$objOutPut, 'Excel5');
                $objWriter->save('php://output');
                break;
        }
    }

    public function setVariable($name, $value) {
        $this->report->arrayVariable[$name]['initialValue'] = $value;
    }

}

require('autoloader.php');
require('../../tecnickcom/tcpdf/tcpdf.php'); // point to tcpdf class previosly instaled , (probaly in composer instalations)
require('../../phpoffice/phpexcel/Classes/PHPExcel.php'); // point to tcpdf class previosly instaled , (probaly in composer instalations)
//require('../TCPDF/tcpdf.php'); // point to tcpdf class previosly instaled , (probaly in stand alone instalations)
// on production using composer instalation is not necessaty 
$report_name = isset($_GET['report']) ? $_GET['report'] : 'testReport.jrxml';  // sql into testReport.txt report do not select any table.
TTransaction::open('dev');
TTransaction::setLogger(new TLoggerHTML('log.html'));
JasperPHP\Report::$proccessintructionsTime = 'inline'; // if uncomented this line intructions are proccessed afte each database row
$jasper = new TJasper($report_name, $_GET);
$jasper->outpage();
