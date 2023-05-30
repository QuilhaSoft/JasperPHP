<?php

namespace JasperPHP;

use \JasperPHP;
use PDO;

/**
 * classe TLabel
 * classe para construção de rótulos de texto
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 * 
 * 2015.03.11 -- criação
 * */
class Detail extends Element {

    public function generate($obj = null) {
        if ($this->children) {
            $dbData = $obj->dbData;
            if (is_array($dbData) || $dbData instanceOf \ArrayAccess) {
                $totalRows = count($dbData);
            } else if ($dbData instanceOf \PDOStatement) {
                $dbData->setFetchMode(PDO::FETCH_OBJ);
                $totalRows = $dbData->rowCount();
            } else {
                $totalRows = 0;
            }
            
            if (count($obj->arrayGroup) > 0) {
                foreach ($obj->arrayGroup as $group) {
                    $groupName = strval($group->attributes()['name']);
                    $obj->arrayVariable[$groupName.'_COUNT']["ans"] = 1;
                    $obj->arrayVariable[$groupName.'_COUNT']['target'] = 1;
                    $obj->arrayVariable[$groupName.'_COUNT']['calculation'] = 'Count';
                    $obj->arrayVariable[$groupName.'_COUNT']['resetType'] = 'Group';
                    $obj->arrayVariable[$groupName.'_COUNT']['resetGroup'] = $groupName;
                    $obj->arrayVariable[$groupName.'_COUNT']['initialValue'] = 1;
                    $group->resetVariables = 'true';
                    $group->started = 'false';
                }
            }

            $previousRow = [];
            $rowIndex = 0;
            
            foreach ($dbData as $row) {
                // convert array to object
                if (!is_object($row) && is_array($row)) {
                    $row = (object)$row;
                }
                
                $obj->variables_calculation($obj, $row);
                $obj->rowData = $row;
                $obj->generateDeferred();

                $rowIndex++;
                if(JasperPHP\Report::$proccessintructionsTime == 'inline'){
                    JasperPHP\Instructions::runInstructions();
                }

                $row->rowIndex = $rowIndex;

                $obj->arrayVariable['REPORT_COUNT']["ans"] = $rowIndex;
                $obj->arrayVariable['REPORT_COUNT']['target'] = $rowIndex;
                $obj->arrayVariable['REPORT_COUNT']['calculation'] = null;
                $obj->arrayVariable['totalRows']["ans"] = $totalRows;
                $obj->arrayVariable['totalRows']["target"] = $totalRows;
                $obj->arrayVariable['totalRows']["calculation"] = null;
                $row->totalRows = $totalRows;
                // test if need group footer (reverse order)
                foreach (array_reverse($obj->arrayGroup) as $group) {
                    $groupExpressionResult = $obj->get_expression($group->groupExpression, $row);
                    if ( (!isset($group->lastResult) || $groupExpressionResult != $group->lastResult || $group->resetVariables == 'true') && ($group->groupHeader)) {
                        if ( $group->groupFooter && $group->started == 'true') {
                            $groupFooter = new GroupFooter($group->groupFooter);
                            $groupFooter->generate(array($obj, $previousRow));
                        }
                    }
                }
                // then make the new headers
                foreach ($obj->arrayGroup as $group) {
                    $groupExpressionResult = $obj->get_expression($group->groupExpression, $row);
                    if ( (!isset($group->lastResult) || $groupExpressionResult != $group->lastResult || $group->resetVariables == 'true') && ($group->groupHeader)) {
                        $group->resetVariables = 'true';
                        $groupHeader = new GroupHeader($group->groupHeader);
                        $groupHeader->generate(array($obj, $row));
                        $group->lastResult = $groupExpressionResult;
                        $group->started = 'true';
                    }
                }
                $background = $obj->getChildByClassName('Background');

                if ($background)
                    $background->generate($obj);

                // armazena no array $results;
                foreach ($this->children as $child) {
                    // se for objeto
                    if (is_object($child)) {
                        $print_expression_result = false;
                        $printWhenExpression = (string) $child->objElement->printWhenExpression;
                        if ($printWhenExpression != '') {
                            
                            $printWhenExpression = $obj->get_expression($printWhenExpression, $row);
                            
                            //echo    'if('.$printWhenExpression.'){$print_expression_result=true;}';
                            eval('if(' . $printWhenExpression . '){$print_expression_result=true;}');
                        } else {
                            $print_expression_result = true;
                        }
                        $height = (string) $child->objElement['height'];
                        if ($print_expression_result == true) {
                            if ($child->objElement['splitType'] == 'Stretch' || $child->objElement['splitType'] == 'Prevent') {
                                JasperPHP\Instructions::addInstruction(array("type" => "PreventY_axis", "y_axis" => $height));
                            }
                            if(JasperPHP\Report::$proccessintructionsTime == 'inline'){
                                JasperPHP\Instructions::runInstructions();
                            }
                            $child->generate(array($obj, $row));
                            if ($child->objElement['splitType'] == 'Stretch' || $child->objElement['splitType'] == 'Prevent') {
                                JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
                            }
                            if(JasperPHP\Report::$proccessintructionsTime == 'inline'){
                                JasperPHP\Instructions::runInstructions();
                            }
                            if ($obj->arrayPageSetting['columnCount'] > 1) {
                                JasperPHP\Instructions::addInstruction(array("type" => "ChangeCollumn"));
                                if (is_int($rowIndex / $obj->arrayPageSetting['columnCount'])) {
                                    JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
                                }
                            }
                        }
                    }
                }
                
                $obj->lastRowData = $obj->rowData;
                $previousRow = $row;

            }
            foreach (array_reverse($obj->arrayGroup) as $group) {
                if ( $group->groupFooter && $group->started == 'true' ) {
                    $groupFooter = new GroupFooter($group->groupFooter);
                    $groupFooter->generate(array($obj, $previousRow));
                }
            }
            
        }
    }

}
