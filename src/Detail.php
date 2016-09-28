<?php
namespace JasperPHP;
use \JasperPHP;
	/**
	* classe TLabel
	* classe para construзгo de rуtulos de texto
	*
	* @author   Rogerio Muniz de Castro <rogerio@singularsistemas.net>
	* @version  2015.03.11
	* @access   restrict
	* 
	* 2015.03.11 -- criaзгo
	**/
	class Detail extends Element
	{
		public function generate($obj = null)
		{
			$dbData = $obj->getDbData();
			if ($this->children)
			{
				$rowIndex = 1;
				$totalRows = $dbData->rowCount();
				$recordObject = array_key_exists('recordObj',$obj->arrayVariable)?$obj->arrayVariable['recordObj']['initialValue']:"stdClass"; 
				while ($row = $dbData->fetchObject($recordObject))
				{
					$row->rowIndex = $rowIndex;
					$row->totalRows = $totalRows;
					$obj->variables_calculation($obj,$row);
					// armazena no array $results;
					foreach ($this->children as $child)
					{
						// se for objeto
						if (is_object($child))
						{
							$print_expression_result = false;
							if($child->objElement->printWhenExpression!=''){
								$printWhenExpression = $child->objElement->printWhenExpression;
								preg_match_all("/P{(\w+)}/",$printWhenExpression ,$matchesP);
								preg_match_all("/F{(\w+)}/",$printWhenExpression ,$matchesF);
								preg_match_all("/V{(\w+)}/",$printWhenExpression ,$matchesV);
								if($matchesP>0){
									foreach($matchesP[1] as $macthP){
										$printWhenExpression = str_ireplace(array('$P{'.$macthP.'}','"'),array($obj->arrayParameter[$macthP],''),$printWhenExpression); 
									}
								}if($matchesF>0){
									foreach($matchesF[1] as $macthF){
										$printWhenExpression = $obj->getValOfField($macthF,$row,$printWhenExpression);
									}
								}
								if($matchesV>0){
									foreach($matchesV[1] as $macthV){
										$printWhenExpression = $obj->getValOfVariable($macthV,$printWhenExpression); 
									}

								}
								eval('if('.$printWhenExpression.'){$print_expression_result=true;}');
							}else{
							  $print_expression_result=true;  
							}
							if($print_expression_result == true){
								if($child->splitType=='Stretch' || $child->splitType=='Prevent'){
									JasperPHP\Pdf::addInstruction(array ("type"=>"PreventY_axis","y_axis"=>$child->height));
								}
								$child->generate(array($obj,$row));
								if($child->splitType=='Stretch' || $child->splitType=='Prevent'){
									JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$child->height));
								}
							}

						}
					}
					$rowIndex++;

				} 

				//$this->close();
			}
		}
	}
?>