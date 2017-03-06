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
class Detail extends Element
{
    public function generate($obj = null)
    {
        $dbData = $obj->getDbData();
        if ($this->children)
        {
            $rowIndex = 1;
            $totalRows = $dbData->rowCount();
            $arrayVariable = ($obj->arrayVariable)?$obj->arrayVariable:array();
            $recordObject = array_key_exists('recordObj',$arrayVariable)?$obj->arrayVariable['recordObj']['initialValue']:"stdClass"; 
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
                        //var_dump((string)$child->objElement->printWhenExpression);
                        //echo     (string)$child->objElement['printWhenExpression']."oi";
                        $printWhenExpression = (string)$child->objElement->printWhenExpression;
                        if($printWhenExpression!=''){


                            //echo $printWhenExpression;
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
                            //echo    'if('.$printWhenExpression.'){$print_expression_result=true;}';
                            eval('if('.$printWhenExpression.'){$print_expression_result=true;}');
                        }else{
                            $print_expression_result=true;  
                        }
                        $height = (string)$child->objElement['height'];
                        if($print_expression_result == true){
                            if($child->objElement['splitType']=='Stretch' || $child->objElement['splitType']=='Prevent'){
                                JasperPHP\Pdf::addInstruction(array ("type"=>"PreventY_axis","y_axis"=>$height));
                            } 
                            $child->generate(array($obj,$row));
                            if($child->objElement['splitType']=='Stretch' || $child->objElement['splitType']=='Prevent'){
                                JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$height));
                            }
                            if($obj->arrayPageSetting['columnCount']>1){
                                JasperPHP\Pdf::addInstruction(array ("type"=>"ChangeCollumn"));
                                if(is_int($rowIndex/$obj->arrayPageSetting['columnCount'])){
                                    JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$height));
                                }

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