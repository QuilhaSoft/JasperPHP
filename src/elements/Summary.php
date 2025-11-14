<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * Summary class
 * This class represents the summary band in a Jasper report.
 */
class Summary extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        if (empty($this->children)) {
            return;
        }

        $band = $this->children[0];
        $height = (string) $band->objElement['height'];
        $splitType = (string) $band->objElement['splitType'];

        if ($splitType == 'Stretch' || $splitType == 'Prevent') {
            Instructions::addInstruction(["type" => "PreventY_axis", "y_axis" => $height]);
        }

        parent::generate();

        if ($splitType == 'Stretch' || $splitType == 'Prevent') {
            Instructions::addInstruction(["type" => "SetY_axis", "y_axis" => $height]);
        }
    }
}

?>