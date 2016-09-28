<?php
namespace JasperPHP;
//use \JasperPHP;
use \PHPExcel;
use \PHPExcel_Settings;
use \PHPExcel_CachedObjectStorageFactory;
	/*
	* classe TConnection
	* gerencia conexões com bancos de dados através de arquivos de configuração.
	*
	* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
	* @version  2015.03.10
	* @access   restrict
	* 
	* 2015.03.10 -- criação
	**/

	final class Excel
	{
		static private $xlsOutPut;
		static private $intructions;
		/*
		* método __construct()
		* não existirão instâncias de TConnection, por isto estamos marcando-o como private
		*/
		private function __construct() {

		}
		public static function prepare(){

			self::$xlsOutPut = new PHPExcel();
			$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
			$cacheSettings = array( ' memoryCacheSize ' => '8MB');
			PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
/*			self::$pdfOutPut->SetLeftMargin((int)$report->arrayPageSetting["leftMargin"]);
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
*/		}


		/*
		* método open()
		* recebe o nome do banco de dados e instancia o objeto PDO correspondente
		*/
		public static function get()
		{
			return self::$xlsOutPut;
		}
	}
?>