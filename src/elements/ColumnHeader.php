<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * ColumnHeader class
 * This class represents the column header band in a Jasper report.
 */
class ColumnHeader extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        $print_expression_result = false;
        $printWhenExpression = (string) $this->objElement->printWhenExpression;

        if ($printWhenExpression != '') {
            $printWhenExpressionEvaluated = $this->report->get_expression($printWhenExpression, $this->report->rowData);
            eval('if(' . $printWhenExpressionEvaluated . '){$print_expression_result=true;}');
        } else {
            $print_expression_result = true;
        }

        if ($print_expression_result) {
            $splitType = (string) $this->children[0]->objElement->splitType;
            $isSplitTypeStretchOrPrevent = ($splitType == 'Stretch' || $splitType == 'Prevent');

            if ($isSplitTypeStretchOrPrevent) {
                Instructions::addInstruction(["type" => "PreventY_axis", "y_axis" => $this->children[0]->objElement['height']]);
            }

            parent::generate();
            Instructions::addInstruction(["type" => "SetY_axis", "y_axis" => $this->children[0]->objElement['height']]);
        }
    }
}

?>