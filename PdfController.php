<?php
// src/Controller/pdfController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use JasperPHP;
use JasperPHP\ado\TTransaction;
use JasperPHP\ado\TLogger;
use JasperPHP\ado\TLoggerHTML;;

class PdfController{
	private $report;
    private $type;
    private $param;
	

    public function outpage($type = 'pdf') {
        $this->report->generate();
        $this->report->out();
        switch ($this->type) {
            case 'pdf':
                $pdf = JasperPHP\Instructions::get();
                return new Response($pdf->Output('report.pdf','I'));
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
	
	/**
    * @Route("/pdf/download/{xmlFile}")
    */
	public function download($xmlFile)
	{
		$GLOBALS['reports'] = array();
        //$xmlFile = 'testReport.jrxml';
		$param  = array();
        $this->type = (array_key_exists('type', $param)) ? $param['type'] : 'pdf';
        //error_reporting(0);
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
		$report_name = isset($_GET['report']) ? $_GET['report'] : 'testReport.jrxml';  // sql into testReport.txt report do not select any table.
		TTransaction::open('dev');	
		TTransaction::setLogger(new TLoggerHTML('log.html'));
		$pdffile = $this->outpage();
		return $pdffile;//$this->file($pdffile);
	}
}