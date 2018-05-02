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
 * */
class ColumnFooter extends Element {

    public function generate($obj = null) {
        $row = is_array($obj) ? $obj[1] : array();
        $obj = is_array($obj) ? $obj[0] : $obj;
        foreach ($this->children as $child) {
            // se for objeto
            if (is_object($child)) {

                $print_expression_result = false;
                //var_dump((string)$child->objElement->printWhenExpression);
                //echo     (string)$child->objElement['printWhenExpression']."oi";
                $printWhenExpression = (string) $child->objElement->printWhenExpression;
                if ($printWhenExpression != '') {


                    //echo $printWhenExpression;
                    preg_match_all("/P{(\w+)}/", $printWhenExpression, $matchesP);
                    preg_match_all("/F{(\w+)}/", $printWhenExpression, $matchesF);
                    preg_match_all("/V{(\w+)}/", $printWhenExpression, $matchesV);
                    if ($matchesP > 0) {
                        foreach ($matchesP[1] as $macthP) {
                            $printWhenExpression = str_ireplace(array('$P{' . $macthP . '}', '"'), array($obj->arrayParameter[$macthP], ''), $printWhenExpression);
                        }
                    }if ($matchesF > 0) {
                        foreach ($matchesF[1] as $macthF) {
                            $printWhenExpression = $obj->getValOfField($macthF, $row, $printWhenExpression);
                        }
                    }
                    if ($matchesV > 0) {
                        foreach ($matchesV[1] as $macthV) {
                            $printWhenExpression = $obj->getValOfVariable($macthV, $printWhenExpression);
                        }
                    }
                    //echo    'if('.$printWhenExpression.'){$print_expression_result=true;}';
                    eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
                } else {
                    $print_expression_result = true;
                }
                if ($print_expression_result == true) {
                    if ($this->children['0']->objElement['splitType'] == 'Stretch' || $this->children['0']->objElement['splitType'] == 'Prevent') {
                        JasperPHP\Pdf::addInstruction(array("type" => "PreventY_axis", "y_axis" => $this->children['0']->objElement['height']));
                    }
                    parent::generate($obj);
                    //var_dump($this->children['0']);
                    JasperPHP\Pdf::addInstruction(array("type" => "SetY_axis", "y_axis" => $this->children['0']->objElement['height']));
                }
            }
        }
    }

}

?>