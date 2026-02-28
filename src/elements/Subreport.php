<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\elements\Report;

/**
 * Subreport class
 * This class handles subreports within a Jasper report.
 */
class Subreport extends Element
{

    public $returnValues;
    public $subreportExpression;
    public $dataSourceExpression;

    public function __construct($ObjElement, $report = null)
    {
        parent::__construct($ObjElement, $report);
    }

    public function generate()
    {
        $rowData = $this->report->rowData;
        $reportInstance = $this->report;
        $this->returnValues = array();


        $print_expression_result = false;
        $printWhenExpression = (string)$this->objElement->reportElement->printWhenExpression;
        if ($printWhenExpression != '') {
            $printWhenExpression = $reportInstance->get_expression($printWhenExpression, $rowData);
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
        if (is_array($rowData)) {
            $rowArray = $rowData;
        } elseif (is_object($rowData)) {
            if (method_exists($rowData, 'toArray')) {
                $rowArray = $rowData->toArray();
            } else {
                $rowArray = get_object_vars($rowData);
            }
        } elseif (!$rowData) {
            $rowArray = [];
        }
        $newParameters = ($rowArray) ? array_merge($reportInstance->arrayParameter, $rowArray) : $reportInstance->arrayParameter;
        //$GLOBALS['reports'][$xmlFile] = (array_key_exists($xmlFile, $GLOBALS['reports'])) ? $GLOBALS['reports'][$xmlFile] : new JasperPHP\Report($xmlFile);
        $report = new Report($xmlFile, $newParameters, $reportInstance, $reportInstance->debugMode); //$GLOBALS['reports'][$xmlFile];
        //$this->children= array($report);
        
        //query in subreport
		$queryString = (string)$report->objElement->queryString??null;
		if(!empty($queryString)){
			$report->setDataSourceConfig($reportInstance->getDataSourceConfig());			
		}
        
        if (preg_match("#^\$F{#", (string)$this->objElement->dataSourceExpression) === 1) {
            $report->dbData = $reportInstance->get_expression((string)$this->objElement->dataSourceExpression, $rowData, null);
        }

        $report->generate();
        foreach ($this->objElement->returnValue as $r) {
            $this->returnValues[] = $r;
        }
        $reportInstance->setReturnVariables($this, $report->arrayVariable);
    }
}
