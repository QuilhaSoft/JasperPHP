<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;

/**
 * Frame class
 * This class represents a frame element in a Jasper report.
 */
class Frame extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        if ($this->children) {
            foreach ($this->children as $child) {
                if (is_object($child)) {
                    $child->generate();
                }
            }
        }
    }
}

?>
