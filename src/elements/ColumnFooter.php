<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * ColumnFooter class
 * This class represents the column footer band in a Jasper report.
 */
class ColumnFooter extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        $rowData = $this->report->lastRowData;
        if (!$rowData) {
            return;
        }

        //revovery report count
		$this->report->arrayVariable['REPORT_COUNT']["ans"] = $rowData->rowIndex;	

        foreach ($this->children as $child) {
            if (is_object($child)) {
                $print_expression_result = false;
                $printWhenExpression = (string) $child->objElement->printWhenExpression;

                if ($printWhenExpression != '') {
                    $printWhenExpressionEvaluated = $this->report->get_expression($printWhenExpression, $rowData);
                    eval('if(' . $printWhenExpressionEvaluated . '){$print_expression_result=true;}');
                } else {
                    $print_expression_result = true;
                }

                if ($print_expression_result) {
                    $splitType = (string) $this->children[0]->objElement['splitType'];
                    $isSplitTypeStretchOrPrevent = ($splitType == 'Stretch' || $splitType == 'Prevent');

                    if ($isSplitTypeStretchOrPrevent) {
                        Instructions::addInstruction(["type" => "PreventY_axis", "y_axis" => $this->children[0]->objElement['height']]);
                    }

                    parent::generate();
                    Instructions::addInstruction(["type" => "SetY_axis", "y_axis" => $this->children[0]->objElement['height']]);
                }
            }
        }
    }
}

?>
