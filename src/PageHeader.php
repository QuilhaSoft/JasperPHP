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
class PageHeader extends Element {

    public function generate($obj = null) {
        $row = (array) $obj->rowData;
        $data = $this->objElement;
        $obj = is_array($obj) ? $obj[0] : $obj;
        $band = $this->children['0'];
        $height = (string) $band->objElement['height'];
        $print_expression_result = false;
        $printWhenExpression = (string) $band->objElement->printWhenExpression;
        
        if ($printWhenExpression != '') {
            $printWhenExpression = $obj->get_expression($printWhenExpression, $row);
            eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
        } else {
            $print_expression_result = true;
        }
        
        if ($print_expression_result == true) {
            parent::generate(array($obj, $row));
            JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
        }
    }

}
