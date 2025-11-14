<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;

/**
 * Band class
 * This class represents a band in a Jasper report, such as title, page header, detail, etc.
 */
class Band extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        if ($this->children) {
            foreach ($this->children as $child) {
                // se for objeto
                if (is_object($child)) {
                    $child->generate();
                }
            }
        }
    }
}

?>