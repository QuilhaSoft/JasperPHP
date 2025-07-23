<?php

namespace JasperPHP;

/**
 * GroupFooter class
 * This class represents the group footer band in a Jasper report.
 */
class GroupFooter extends Element {

    public $printWhenExpression;

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
                    // WARNING: Using eval() can be a security risk and makes debugging difficult.
                    // A more robust solution would involve parsing and evaluating expressions without eval.
                    eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
                } else {
                    $print_expression_result = true;
                }
                if ($print_expression_result) {
                    $isSplitTypeStretchOrPrevent = ($child->objElement['splitType'] == 'Stretch' || $child->objElement['splitType'] == 'Prevent');
                    if ($isSplitTypeStretchOrPrevent) {
                        Instructions::addInstruction(array("type" => "PreventY_axis", "y_axis" => $child->objElement['height']));
                    }
                    parent::generate(array($obj,$row));
                    Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $child->objElement['height']));
                }
            }
        }
    }

}