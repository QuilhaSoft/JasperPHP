<?php

namespace JasperPHP;

/**
 * Title class
 * This class represents the title band in a Jasper report.
 */
class Title extends Element {

    public function generate($obj = null) {
        $dbData = $obj->dbData;
        $arrayVariable = $obj->arrayVariable ?? [];
        $recordObject = $arrayVariable['recordObj']['initialValue'] ?? "stdClass";
        $row = (is_array($dbData) || $dbData instanceOf \ArrayAccess) ? ( isset($dbData[0]) ? $dbData[0] : array() ) : $obj->rowData;
        
        if (!$row) {
            $row = array();
        }
        
        
        
        foreach ($this->children as $child) {
            // se for objeto
            if (is_object($child)) {
                $height = (string) $this->children['0']->objElement['height'];
                parent::generate(array($obj, $row));
                Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
            }
        }
    }

}