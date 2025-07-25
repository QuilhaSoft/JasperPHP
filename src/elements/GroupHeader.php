<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * GroupHeader class
 * This class represents the group header band in a Jasper report.
 */
class GroupHeader extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        foreach ($this->children as $child) {
            if (is_object($child)) {
                $print_expression_result = false;
                $printWhenExpression = (string) $child->objElement->printWhenExpression;

                if ($printWhenExpression != '') {
                    $printWhenExpressionEvaluated = $this->report->get_expression($printWhenExpression, $this->report->rowData);
                    eval('if(' . $printWhenExpressionEvaluated . '){$print_expression_result=true;}');
                } else {
                    $print_expression_result = true;
                }

                if ($print_expression_result) {
                    $splitType = (string) $child->objElement['splitType'];
                    $isSplitTypeStretchOrPrevent = ($splitType == 'Stretch' || $splitType == 'Prevent');

                    if ($isSplitTypeStretchOrPrevent) {
                        Instructions::addInstruction(["type" => "PreventY_axis", "y_axis" => $child->objElement['height']]);
                    }

                    // The original code called parent::generate, which is incorrect for a band.
                    // It should call the child's generate method.
                    $child->generate();
                    
                    Instructions::addInstruction(["type" => "SetY_axis", "y_axis" => $child->objElement['height']]);
                }
            }
        }
    }
}