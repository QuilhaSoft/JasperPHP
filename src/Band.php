<?php

namespace JasperPHP;

/**
 * Band class
 * This class represents a band in a Jasper report, such as title, page header, detail, etc.
 */
class Band extends Element {

    public function generate($obj = null) {
        $row = is_array($obj) ? $obj[1] : array();
        $obj = is_array($obj) ? $obj[0] : $obj;
        if ($this->children) {
            foreach ($this->children as $child) {
                // se for objeto
                if (is_object($child)) {
                    $child->generate(array($obj, $row));
                }
            }
        }
    }

}

?>