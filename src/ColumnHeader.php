<?php

namespace JasperPHP;

/**
 * ColumnHeader class
 * This class represents the column header band in a Jasper report.
 */
class ColumnHeader extends Element {

    public $printWhenExpression;

    public function generate($obj = null) {
        $row = is_array($obj) ? $obj[1] : array();
        $obj = is_array($obj) ? $obj[0] : $obj;
        $print_expression_result = false;
        //var_dump((string)$child->objElement->printWhenExpression);
        //echo     (string)$child->objElement['printWhenExpression']."oi";
        $printWhenExpression = (string) $this->objElement->printWhenExpression;
        if ($printWhenExpression != '') {

            $printWhenExpression = $obj->get_expression($printWhenExpression, $row);
            // WARNING: Using eval() can be a security risk and makes debugging difficult.
            // A more robust solution would involve parsing and evaluating expressions without eval.
            eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
        } else {
            $print_expression_result = true;
        }
        if ($print_expression_result) {
            $isSplitTypeStretchOrPrevent = ($this->children['0']->objElement->splitType == 'Stretch' || $this->children['0']->objElement->splitType == 'Prevent');
            if ($isSplitTypeStretchOrPrevent) {
                Instructions::addInstruction(array("type" => "PreventY_axis", "y_axis" => $this->children['0']->objElement['height']));
            }
            parent::generate($obj);
            Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $this->children['0']->objElement['height']));
        }
    }

}

?>