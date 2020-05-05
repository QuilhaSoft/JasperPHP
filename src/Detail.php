<?php

namespace JasperPHP;

use \JasperPHP;

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
        $dbData = $obj->dbData;
        if ($this->children) {
            $rowIndex = 1;
            $totalRows = is_array($dbData) ? count($dbData) : $dbData->rowCount();

            $row = is_array($dbData) ? $dbData[0] : $obj->rowData; // $dbData->fetchObject($recordObject);

            while ($row) {

                $row->rowIndex = $rowIndex;
                $obj->arrayVariable['REPORT_COUNT']["ans"] = $rowIndex;
                $obj->arrayVariable['REPORT_COUNT']['target'] = $rowIndex;
                $obj->arrayVariable['REPORT_COUNT']['calculation'] = null;
                $obj->arrayVariable['totalRows']["ans"] = $totalRows;
                $obj->arrayVariable['totalRows']["target"] = $totalRows;
                $obj->arrayVariable['totalRows']["calculation"] = null;
                $row->totalRows = $totalRows;
                $obj->variables_calculation($obj, $row);
                if (count($obj->arrayGroup) > 0) {
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
                            $child->generate(array($obj, $row));
                            if ($child->objElement['splitType'] == 'Stretch' || $child->objElement['splitType'] == 'Prevent') {
                                JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
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
                $arrayVariable = ($obj->arrayVariable) ? $obj->arrayVariable : array();
                $recordObject = array_key_exists('recordObj', $arrayVariable) ? $obj->arrayVariable['recordObj']['initialValue'] : "stdClass";

                $row = ( is_array($dbData) ) ? (array_key_exists($rowIndex, $dbData)) ? $dbData[$rowIndex] : null : $dbData->fetchObject($recordObject);
                //echo $rowIndex;
                if (count($obj->arrayGroup) > 0) {
                    foreach ($obj->arrayGroup as $group) {
                        preg_match_all("/F{(\w+)}/", $group->groupExpression, $matchesF);
                        $groupExpression = $matchesF[1][0];
                        if (($obj->rowData->$groupExpression != $row->$groupExpression)  || ($obj->rowData->$groupExpression==NULL)) {
                            $groupFooter = new GroupFooter($group->groupFooter);
                            $groupFooter->generate(array($obj, $obj->rowData));
                            $group->resetVariables = 'true';
                        }
                    }
                }


                $obj->rowData = $row;
                $rowIndex++;
            }

            //$this->close();
        }
    }

}
