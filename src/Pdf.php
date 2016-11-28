<?php
namespace JasperPHP;
use \JasperPHP;
use \TCPDF;
	/*
	* classe Pdf
	* 
	*
	* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
	* @version  2015.03.10
	* @access   restrict
	* 
	* 2015.03.10 -- criação
	**/

	final class Pdf
	{
		static private $pdfOutPut;
		static private $fontdir;
		static private $intructions;
		/*
		* método __construct()
		* não existirão instâncias de TConnection, por isto estamos marcando-o como private
		*/
		private function __construct() {

		}
		public static function prepare($report){
			if($report->arrayPageSetting["orientation"]=="Landscape"){
				self::$pdfOutPut = new TCPDF($report->arrayPageSetting["orientation"],'pt',array( intval($report->arrayPageSetting["pageHeight"]),intval($report->arrayPageSetting["pageWidth"])),true);
			}else{
				self::$pdfOutPut=new TCPDF($report->arrayPageSetting["orientation"],'pt',array(intval($report->arrayPageSetting["pageWidth"]),intval($report->arrayPageSetting["pageHeight"])),true);
			}
			self::$pdfOutPut->SetLeftMargin((int)$report->arrayPageSetting["leftMargin"]);
			self::$pdfOutPut->SetRightMargin((int)$report->arrayPageSetting["rightMargin"]);
			self::$pdfOutPut->SetTopMargin((int)$report->arrayPageSetting["topMargin"]);
			self::$pdfOutPut->SetAutoPageBreak(true,(int)$report->arrayPageSetting["bottomMargin"]/2);
			//self::$pdfOutPut->AliasNumPage();
			self::$pdfOutPut->setPrintHeader(false);
			self::$pdfOutPut->setPrintFooter(false); 
			self::$pdfOutPut->AddPage();
			self::$pdfOutPut->setPage(1,true);
			
			if(self::$fontdir=="")
				self::$fontdir=dirname(__FILE__)."/tcpdf/fonts";
		}

		public static function addInstruction($instruction){
			self::$intructions[] = $instruction;
		}

		/*
		* método open()
		* recebe o nome do banco de dados e instancia o objeto PDO correspondente
		*/
		public static function get()
		{
			return self::$pdfOutPut;
		}
		public static function getInstructions(){
			return self::$intructions;
		}
		public static function clearInstructrions(){
		   self::$intructions = array(); 
		}
	}
?>