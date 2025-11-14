<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * Title class
 * This class represents the title band in a Jasper report.
 */
class Title extends Element {

    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }


    public function generate() {
        $dbData = $this->report->dbData;
        $arrayVariable = $this->report->arrayVariable;
        $recordObject = array_key_exists('recordObj', $arrayVariable) ? $arrayVariable['recordObj']['initialValue'] : 'stdClass';
        $row = (is_array($dbData) || $dbData instanceof \ArrayAccess) ? ( isset($dbData[0]) ? $dbData[0] : array() ) : $this->report->rowData;
        
        if (!$row) {
            $row = array();
        }
        
        foreach ($this->children as $child) {
            // se for objeto
            if (is_object($child)) {
                $height = (string) $this->children['0']->objElement['height'];
                parent::generate();
                Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
            }
        }
    }

}