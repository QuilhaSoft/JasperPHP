<?php

namespace JasperPHP;

use \JasperPHP;

/**
 * classe TLabel
 * classe para construção de rótulos de texto
 *
 * @author   Leandro Gama <gamadoleo@hotmail.com>
 * @version  2021.06.18
 * @access   restrict
 * 
 * 2021.06.18 -- criação
 * */
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
