<?php

namespace JasperPHP;

/**
 * Summary class
 * This class represents the summary band in a Jasper report.
 */
class Summary extends Element {

    public function generate($dbData = null) {
        $height = (string) $this->children['0']->objElement['height'];
        if ($this->children['0']->splitType == 'Stretch' || $this->children['0']->splitType == 'Prevent') {
            Instructions::addInstruction(array("type" => "PreventY_axis", "y_axis" => $height));
        }
        parent::generate($dbData);
        if ($this->children['0']->splitType == 'Stretch' || $this->children['0']->splitType == 'Prevent') {
            Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
        }
    }

}

?>