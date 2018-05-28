<?php
namespace JasperPHP;
use \JasperPHP;
	/**
	* classe TLabel
	* classe para construção de rótulos de texto
	*
	* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
	* @version  2015.03.11
	* @access   restrict
	* 
	* 2015.03.11 -- criação
	**/
	class Breaker extends Element
	{

		public function generate($obj = null)
		{
			$rowData = is_array($obj)?$obj[1]:null;
			$obj = is_array($obj)?$obj[0]:$obj; 
			$data = $this->objElement;
			$printWhenExpression = $data->reportElement->printWhenExpression;
			$printWhenExpression = $data->reportElement->printWhenExpression;
			preg_match_all("/P{(\w+)}/",$printWhenExpression ,$matchesP);
			preg_match_all("/F{(\w+)}/",$printWhenExpression ,$matchesF);
			preg_match_all("/V{(\w+)}/",$printWhenExpression ,$matchesV);
			if($matchesP>0){
				foreach($matchesP[1] as $macthP){
					$printWhenExpression = str_ireplace(array('$P{'.$macthP.'}','"'),array($obj->arrayParameter[$macthP],''),$printWhenExpression); 
				}
			}
			if($matchesF>0){
				foreach($matchesF[1] as $macthF){
					$printWhenExpression = str_ireplace(array('$F{'.$macthF.'}','"'),array(utf8_encode($rowData->$macthF),''),$printWhenExpression); 
				}
			}
			if($matchesV>0){
				foreach($matchesV[1] as $macthV){
					$printWhenExpression = $obj->getValOfVariable($macthV,$printWhenExpression); 
				}

			}
			JasperPHP\Pdf::addInstruction(array("type"=>"break","printWhenExpression"=>$printWhenExpression.""));

			parent::generate($obj);
		}
	}
?>