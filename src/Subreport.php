<?php
namespace JasperPHP;

/**
 * Subreport class
 * This class handles subreports within a Jasper report.
 */
class Subreport extends Element
{

    public $returnValues;
    public $subreportExpression;
    public $dataSourceExpression;

    public function generate($obj = null)
    {
        $rowArray = [];
        $this->returnValues = array();
        $row = is_object($obj) ? $_POST : $obj[1];
        $obj = is_array($obj) ? $obj[0] : $obj;
        
        
        $print_expression_result = false;
        $printWhenExpression = (string)$this->objElement->reportElement->printWhenExpression;
        if ($printWhenExpression != '') {
            $printWhenExpression = $obj->get_expression($printWhenExpression, $row);
            // WARNING: Using eval() can be a security risk and makes debugging difficult.
            // A more robust solution would involve parsing and evaluating expressions without eval.
            eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
        } else {
            $print_expression_result = true;
        }
        if ($print_expression_result !== true) {
            return;
        }
        
        $xmlFile = (string) $this->objElement->subreportExpression;
        $xmlFile = str_ireplace(array('"'), array(''), $xmlFile);
        //$rowArray =is_array($row)?$row:get_object_vars($row);
        if (is_array($row)) {
            $rowArray = $row;
        } elseif (is_object($row)) {
            if (method_exists($row, 'toArray')) {
                $rowArray = $row->toArray();
            } else {
                $rowArray = get_object_vars($row);
            }
        }
        $newParameters = ($rowArray) ? array_merge($obj->arrayParameter, $rowArray) : $obj->arrayParameter;
        //$GLOBALS['reports'][$xmlFile] = (array_key_exists($xmlFile, $GLOBALS['reports'])) ? $GLOBALS['reports'][$xmlFile] : new JasperPHP\Report($xmlFile);
        $report = new Report($xmlFile, $newParameters); //$GLOBALS['reports'][$xmlFile];
        //$this->children= array($report);
        
        if ( preg_match("#^\$F{#", (string)$this->objElement->dataSourceExpression) === 1 ) {
            $report->dbData = $obj->get_expression((string)$this->objElement->dataSourceExpression,$row,null);
        }

        $report->generate($obj ?? []);
        foreach ($this->objElement->returnValue as $r) {
            $this->returnValues[] = $r;
        }
        $obj->setReturnVariables($this, $report->arrayVariable);
    }
}

?>
