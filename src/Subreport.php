<?php
namespace JasperPHP;
use \JasperPHP;
	/**
	* classe TLabel
	* classe para construзгo de rуtulos de texto
	*
	* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
	* @version  2015.03.11
	* @access   restrict
	* 
	* 2015.03.11 -- criaзгo
	**/
	class Subreport extends Element
	{
		public $returnValues;


		public function generate($obj = null)
		{
			$row = is_object($obj)?$_POST:$obj[1];
			$obj = is_array($obj)?$obj[0]:$obj;
			$xmlfile = (string)$this->objElement->subreportExpression;
			//$rowArray =is_array($row)?$row:get_object_vars($row);
            if(is_array($row))
            {
                $rowArray = $row;
            }
            elseif(is_object( $row ))
            {
                if(method_exists($row,'toArray'))
                {
                    $rowArray = $row->toArray();
                }
                else
                {
                    $rowArray = get_object_vars($row);
                }
            }
			$newParameters = ($rowArray)?array_merge($obj->arrayParameter,$rowArray):$obj->arrayParameter;
			$report = new JasperPHP\Report($xmlfile,$newParameters);
			//$this->children= array($report);
			$report->generate();
			foreach($this->objElement->returnValue as $r){
				$this->returnValues[] = $r; 
			}
			$obj->setReturnVariables($this,$report->arrayVariable);
		}
	}
?>