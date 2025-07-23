<?php

namespace JasperPHP;

/**
 * PageFooter class
 * This class represents the page footer band in a Jasper report.
 */
class PageFooter extends Element {

    public function generate($obj = null) {
        $dbData = $obj->dbData;
        $arrayVariable = $obj->arrayVariable ?? [];
        $recordObject = $arrayVariable['recordObj']['initialValue'] ?? "stdClass";
        // $rowIndex = 0;
        $row = (is_array($dbData) || $dbData instanceOf \ArrayAccess) ? $dbData[0] : $obj->rowData;
        //$row = ( is_array($dbData) ) ? (array_key_exists($rowIndex, $dbData)) ? $dbData[$rowIndex] : array() : $obj->lastRowData;
        if (!$row) {
            $row = array();
        }
        
        $height = (string) $this->children['0']->objElement['height'];
        Instructions::addInstruction(array("type" => "resetY_axis"));
        Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => ($obj->arrayPageSetting["pageHeight"] - $obj->arrayPageSetting["topMargin"] - $this->children['0']->height - $obj->arrayPageSetting["bottomMargin"])));
        Instructions::$processingPageFooter = true;
        parent::generate(array($obj, $row));
        Instructions::$processingPageFooter = false;
        
        Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
    }

}
