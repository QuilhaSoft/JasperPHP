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
class Breaker extends Element {

    public function generate($obj = null) {
        $row = is_array($obj) ? $obj[1] : null;
        $obj = is_array($obj) ? $obj[0] : $obj;
        $data = $this->objElement;
        $print_expression_result = false;
        $printWhenExpression = (string) $data->reportElement->printWhenExpression;
        if ($printWhenExpression != '') {

            $printWhenExpression = $obj->get_expression($printWhenExpression, $row);
            eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
        } else {
            $print_expression_result = true;
        }
        if ($print_expression_result == true) {
            JasperPHP\Instructions::addInstruction(array("type" => "break", "printWhenExpression" => $printWhenExpression . ""));
        }
        parent::generate($obj);
    }

}

?>