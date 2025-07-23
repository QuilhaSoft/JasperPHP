<?php

namespace JasperPHP;

/**
 * Frame class
 * This class represents a frame element in a Jasper report.
 */
class Frame extends Element {

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
