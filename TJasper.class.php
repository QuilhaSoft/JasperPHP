<?php
use JasperPHP\Report;
use JasperPHP\Report2XLS;
use JasperPHP\ado\TTransaction;
use JasperPHP\ado\TLoggerHTML;

//use PHPexcel as PHPexcel;
/**
* classe TJasper
* encapsula uma aчуo
*
* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
* @version  2015.03.11
* @access   restrict
* 
* 2015.03.11 -- criaчуo
**/
class TJasper
{
	private $report;
	private $type;

	/**
	* mщtodo __construct()
	* instancia uma nova aчуo
	* @param $action = mщtodo a ser executado
	*/
	public function __construct($jrxml,$param)
	{
		$xmlFile=  $jrxml;
		$this->type = (array_key_exists('type',$param))?$param['type']:'pdf';
		error_reporting(0);
		switch ($this->type)
		{
			case 'pdf': 
				$this->report =new JasperPHP\Report($xmlFile,$param);
				JasperPHP\Pdf::prepare($this->report);
				break;
			case 'xls':
				JasperPHP\Excel::prepare();
				$this->report =new JasperPHP\Report2XLS($xmlFile,$param);
				
				break;
		}
	}
	public function outpage($type='pdf'){
		$this->report->generate();
		$this->report->out();
		switch ($this->type)
		{
			case 'pdf':
				$pdf  = JasperPHP\Pdf::get();
				$pdf->Output('Relatorio.pdf',"I");
				break;
			case 'xls':
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="01simple.xls"');
				header('Cache-Control: max-age=0');
				// If you're serving to IE 9, then the following may be needed
				header('Cache-Control: max-age=1');
				// If you're serving to IE over SSL, then the following may be needed
				header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
				header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
				header ('Pragma: public'); // HTTP/1.0
				$objWriter = PHPExcel_IOFactory::createWriter($this->report->wb, 'Excel5');
				$objWriter->save('php://output');
			break;
		}
		
	}
	public function setVariable($name,$value){
		$this->report->arrayVariable[$name]['initialValue'] = $value ;
	}
}
require('autoloader.php') ;
TTransaction::open('dev');
$jasper = new TJasper('template.jrxml',$_GET);
$jasper->outpage();
?>