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
        $rowIndex = 0;
        $row = $obj->lastRowData;
        if (!$row) {return;}
        //if (!$row) {
        //    $row = array();
        //}
        $obj = is_array($obj) ? $obj[0] : $obj;
        foreach ($this->children as $child) {
            // se for objeto
            if (is_object($child)) {

                $print_expression_result = false;
                //var_dump((string)$child->objElement->printWhenExpression);
                //echo     (string)$child->objElement['printWhenExpression']."oi";
                $printWhenExpression = (string) $child->objElement->printWhenExpression;
                if ($printWhenExpression != '') {

                    $printWhenExpression = $obj->get_expression($printWhenExpression, $row);
                    eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
                    
                } else {
                    $print_expression_result = true;
                }
                if ($print_expression_result == true) {
                    if ($this->children['0']->objElement['splitType'] == 'Stretch' || $this->children['0']->objElement['splitType'] == 'Prevent') {
                        JasperPHP\Instructions::addInstruction(array("type" => "PreventY_axis", "y_axis" => $this->children['0']->objElement['height']));
                    }
                    parent::generate(array($obj,$row));
                    //var_dump($this->children['0']);
                    JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $this->children['0']->objElement['height']));
                }
            }
        }
    }

}

?>
