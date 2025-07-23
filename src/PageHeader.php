<?php

namespace JasperPHP;

/**
 * PageHeader class
 * This class represents the page header band in a Jasper report.
 */
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
            // WARNING: Using eval() can be a security risk and makes debugging difficult.
            // A more robust solution would involve parsing and evaluating expressions without eval.
            eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
        } else {
            $print_expression_result = true;
        }
        
        if ($print_expression_result) {
            parent::generate(array($obj, $row));
            Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
        }
    }

}
