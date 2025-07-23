<?php

namespace JasperPHP;

/**
 * Detail class
 * This class represents the detail band in a Jasper report.
 */
class Detail extends Element {

    public $printWhenExpression;

    public function generate($obj = null) {
        $dbData = $obj->dbData;
        if ($this->children) {
            $rowIndex = 1;
            $totalRows = is_countable($dbData) ? count($dbData) : $dbData->rowCount();

            $isDbDataArrayOrAccess = (is_array($dbData) || $dbData instanceof \ArrayAccess);

            $row = $isDbDataArrayOrAccess ? $dbData[0] : $obj->rowData;

            $obj->variables_calculation($obj, $row);
            while ($row) {
                if(Report::$proccessintructionsTime == 'inline'){
                    Instructions::runInstructions();
                }

                // convert array to object
                if (is_array($row)) {
                    $row = (object)$row;
                }
                
                $row->rowIndex = $rowIndex;

                $obj->arrayVariable['REPORT_COUNT']["ans"] = $rowIndex;
                $obj->arrayVariable['REPORT_COUNT']['target'] = $rowIndex;
                $obj->arrayVariable['REPORT_COUNT']['calculation'] = null;
                $obj->arrayVariable['totalRows']["ans"] = $totalRows;
                $obj->arrayVariable['totalRows']["target"] = $totalRows;
                $obj->arrayVariable['totalRows']["calculation"] = null;
                $row->totalRows = $totalRows;
                if (!empty($obj->arrayGroup)) {
                    foreach ($obj->arrayGroup as $group) {
                        preg_match_all("/F{(\w+)}/", $group->groupExpression, $matchesF);
                        $groupExpression = $matchesF[1][0];
                        if (($rowIndex == 1 || $group->resetVariables == 'true') && ($group->groupHeader)) {
                            $groupHeader = new GroupHeader($group->groupHeader);
                            $groupHeader->generate(array($obj, $row));
                            $group->resetVariables = 'false';
                        }
                    }
                }
                $background = $obj->getChildByClassName('Background');

                if ($background) {
                    $background->generate($obj);
                }

                // armazena no array $results;
                foreach ($this->children as $child) {
                    // se for objeto
                    if (is_object($child)) {
                        $print_expression_result = false;
                        $printWhenExpression = (string) $child->objElement->printWhenExpression;
                        if ($printWhenExpression != '') {
                            
                            $printWhenExpression = $obj->get_expression($printWhenExpression, $row);
                            
                            //echo    'if('.$printWhenExpression.'){$print_expression_result=true;}';
                            // WARNING: Using eval() can be a security risk and makes debugging difficult.
                            // A more robust solution would involve parsing and evaluating expressions without eval.
                            eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
                        } else {
                            $print_expression_result = true;
                        }
                        $height = (string) $child->objElement['height'];
                        if ($print_expression_result) {
                            $isSplitTypeStretchOrPrevent = ($child->objElement['splitType'] == 'Stretch' || $child->objElement['splitType'] == 'Prevent');
                        if ($isSplitTypeStretchOrPrevent) {
                            Instructions::addInstruction(array("type" => "PreventY_axis", "y_axis" => $height));
                        }
                        if(Report::$proccessintructionsTime == 'inline'){
                            Instructions::runInstructions();
                        }
                        $child->generate(array($obj, $row));
                        if ($isSplitTypeStretchOrPrevent) {
                            Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
                        }
                        if(Report::$proccessintructionsTime == 'inline'){
                            Instructions::runInstructions();
                        }
                            if ($obj->arrayPageSetting['columnCount'] > 1) {
                                Instructions::addInstruction(array("type" => "ChangeCollumn"));
                                if (($rowIndex % $obj->arrayPageSetting['columnCount']) === 0) {
                                Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
                            }
                            }
                        }
                    }
                }
                
                $arrayVariable = $obj->arrayVariable ?? [];
                $recordObject = $arrayVariable['recordObj']['initialValue'] ?? "stdClass";
                $obj->lastRowData = $obj->rowData;
                $row = $isDbDataArrayOrAccess ? ($dbData[$rowIndex] ?? null) : $dbData->fetchObject($recordObject);
                //echo $rowIndex;
                
                $obj->rowData = $row;
                $obj->variables_calculation($obj, $row);
                $rowIndex++;
            }

            //$this->close();
        }
    }

}
