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
class GroupFooter extends Element {

    public function generate($obj = null) {
        $row = is_array($obj) ? $obj[1] : array();
        $obj = is_array($obj) ? $obj[0] : $obj;
        foreach ($this->children as $child) {
            // se for objeto
            if (is_object($child)) {

                $print_expression_result = false;
                $printWhenExpression = (string) $child->objElement->printWhenExpression;
                if ($printWhenExpression != '') {
                    $printWhenExpression = $obj->get_expression($printWhenExpression,$row);
                    eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
                } else {
                    $print_expression_result = true;
                }
                if ($print_expression_result == true) {
                    if ($child->objElement['splitType'] == 'Stretch' || $child->objElement['splitType'] == 'Prevent') {
                        JasperPHP\Instructions::addInstruction(array("type" => "PreventY_axis", "y_axis" => $child->objElement['height']));
                    }
                    parent::generate(array($obj,$row));
                    JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $child->objElement['height']));
                }
            }
        }
    }

}