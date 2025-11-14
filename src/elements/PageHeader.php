<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * PageHeader class
 * This class represents the page header band in a Jasper report.
 */
class PageHeader extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        $rowData = (array) $this->report->rowData;
        $band = $this->children[0];
        $height = (string) $band->objElement['height'];

        $print_expression_result = false;
        $printWhenExpression = (string) $band->objElement->printWhenExpression;

        if ($printWhenExpression != '') {
            $printWhenExpressionEvaluated = $this->report->get_expression($printWhenExpression, $rowData);
            eval('if(' . $printWhenExpressionEvaluated . '){$print_expression_result=true;}');
        } else {
            $print_expression_result = true;
        }

        if ($print_expression_result) {
            parent::generate();
            Instructions::addInstruction(["type" => "SetY_axis", "y_axis" => $height]);
        }
    }
}
