<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;
use JasperPHP\elements\Report;
use JasperPHP\elements\GroupHeader;
use JasperPHP\elements\GroupFooter;
use JasperPHP\core\Background;

/**
 * Detail class
 * This class represents the detail band in a Jasper report.
 */
class Detail extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        $dbData = $this->report->dbData;
        if (!$this->children || !$dbData) {
            return;
        }

        $rowIndex = 1;
        $totalRows = is_countable($dbData) ? count($dbData) : ($dbData->rowCount() ?: 0);
        $isDbDataArrayOrAccess = (is_array($dbData) || $dbData instanceof \ArrayAccess);
        
        // Initialize with the first row
        $this->report->rowData = $isDbDataArrayOrAccess ? ($dbData[0] ?? null) : $dbData->fetchObject($this->report->arrayVariable['recordObj']['initialValue'] ?? "stdClass");
        
        $this->report->variables_calculation($this->report->rowData);

        while ($this->report->rowData) {
            if (Report::$proccessintructionsTime == 'inline') {
                Instructions::runInstructions();
            }

            if (is_array($this->report->rowData)) {
                $this->report->rowData = (object) $this->report->rowData;
            }
            
            $this->report->rowData->rowIndex = $rowIndex;
            $this->report->arrayVariable['REPORT_COUNT']["ans"] = $rowIndex;
            $this->report->arrayVariable['totalRows']["ans"] = $totalRows;

            // Group Headers
            if (!empty($this->report->arrayGroup)) {
                foreach ($this->report->arrayGroup as $group) {
                    if (($rowIndex == 1 || $group->resetVariables == 'true') && ($group->groupHeader)) {
                        $groupHeader = new GroupHeader($group->groupHeader, $this->report);
                        $groupHeader->generate();
                        $group->resetVariables = 'false';
                    }
                }
            }

            // Background
            $background = $this->report->getChildByClassName('Background');
            if ($background) {
                $background->generate();
            }

            // Detail content
            foreach ($this->children as $child) {
                if (is_object($child)) {
                    $print_expression_result = $this->evaluatePrintWhenExpression($child, $this->report->rowData);
                    
                    if ($print_expression_result) {
                        $this->generateChildElement($child);
                    }
                }
            }

            // Prepare for next iteration
            $this->report->lastRowData = $this->report->rowData;
            $recordObject = $this->report->arrayVariable['recordObj']['initialValue'] ?? "stdClass";
            $this->report->rowData = $isDbDataArrayOrAccess ? ($dbData[$rowIndex] ?? null) : $dbData->fetchObject($recordObject);
            
            if($this->report->rowData) {
                if (isset($this->report->lastRowData) && !empty($this->report->arrayGroup)) {
                    foreach ($this->report->arrayGroup as $group) {
                        if (isset($group->groupExpression) && isset($group->groupFooter)) {
                            $currentGroupValue = $this->report->get_expression($group->groupExpression, $this->report->rowData);
                            $previousGroupValue = $this->report->get_expression($group->groupExpression, $this->report->lastRowData);

                            if ($currentGroupValue != $previousGroupValue) {
                                $groupFooter = new \JasperPHP\elements\GroupFooter($group->groupFooter, $this->report);
                                $groupFooter->generate();
                                $group->resetVariables = 'true';
                            }
                        }
                    }
                }
                $this->report->variables_calculation($this->report->rowData);
            }
            $rowIndex++;
        }
    }

    private function evaluatePrintWhenExpression($element, $rowData)
    {
        $printWhenExpression = (string) $element->objElement->printWhenExpression;
        if ($printWhenExpression != '') {
            $expression = $this->report->get_expression($printWhenExpression, $rowData);
            $result = false;
            $oldErrorReporting = error_reporting(0); // Temporarily disable error reporting
        try {
            eval('if(' . $expression . '){$result=true;}');
        } catch (\ParseError $e) {
            $this->report->addDebugMessage("Erro de Parse na expressão (Detail): " . $expression . " - " . $e->getMessage());
        } finally {
            error_reporting($oldErrorReporting); // Restore original error reporting
        }
            return $result;
        }
        return true;
    }

    private function generateChildElement($element)
    {
        $height = (string) $element->objElement['height'];
        $splitType = (string) $element->objElement['splitType'];
        $isSplitTypeStretchOrPrevent = ($splitType == 'Stretch' || $splitType == 'Prevent');

        if ($isSplitTypeStretchOrPrevent) {
            Instructions::addInstruction(["type" => "PreventY_axis", "y_axis" => $height]);
        }

        if (Report::$proccessintructionsTime == 'inline') {
            Instructions::runInstructions();
        }

        $element->generate();

        if ($isSplitTypeStretchOrPrevent) {
            Instructions::addInstruction(["type" => "SetY_axis", "y_axis" => $height]);
        }
        
        if (Report::$proccessintructionsTime == 'inline') {
            Instructions::runInstructions();
        }

        if ($this->report->arrayPageSetting['columnCount'] > 1) {
            Instructions::addInstruction(["type" => "ChangeCollumn"]);
            if (($this->report->rowData->rowIndex % $this->report->arrayPageSetting['columnCount']) === 0) {
                Instructions::addInstruction(["type" => "SetY_axis", "y_axis" => $height]);
            }
        }
    }
}
