<?php

namespace JasperPHP;

/**
 * Breaker class
 * This class handles page breaks and column breaks in a Jasper report.
 */
class Breaker extends Element
{
    public $printWhenExpression;

    public function generate($obj = null)
    {
        $row = is_array($obj) ? $obj[1] : null;
        $obj = is_array($obj) ? $obj[0] : $obj;
        $data = $this->objElement;
        $print_expression_result = false;
        $printWhenExpression = (string) $data->reportElement->printWhenExpression;
        $pageFooter = $obj->getChildByClassName('PageFooter');


        if ($printWhenExpression != '') {

            $printWhenExpression = $obj->get_expression($printWhenExpression, $row);
            eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
        } else {
            $print_expression_result = true;
        }
        if ($print_expression_result == true && Report::$proccessintructionsTime == 'inline') {
            
            if ($pageFooter)
                $pageFooter->generate($obj,$row);
            //Instructions::addInstruction(array("type" => "break", "printWhenExpression" => $printWhenExpression . ""));
            parent::generate($obj);
            Instructions::addInstruction(array("type" => "resetY_axis"));
            Instructions::$currrentPage++;
            Instructions::addInstruction(array("type" => "AddPage"));
            Instructions::addInstruction(array("type" => "setPage", "value" => Instructions::$currrentPage, 'resetMargins' => false));
            $pageHeader = $obj->getChildByClassName('PageHeader');
            //if (JasperPHP\Pdf::$print_expression_result == true) {
            if ($pageHeader)
                $pageHeader->generate($obj,$row);
            //}

            Instructions::runInstructions();
        }else{
            Instructions::addInstruction(array("type" => "break", "printWhenExpression" => $printWhenExpression . ""));
            parent::generate($obj);
        }

    }
}
