<?php

namespace JasperPHP;

use JasperPHP;

/**
 * classe TLabel
 * classe para construção de rótulos de texto
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 * 
 * 2015.03.11 -- criação
 * */
class Summary extends Element {

    public function generate($dbData = null) {
        $height = (string) $this->children['0']->objElement['height'];
        if ($this->children['0']->splitType == 'Stretch' || $this->children['0']->splitType == 'Prevent') {
            JasperPHP\Instructions::addInstruction(array("type" => "PreventY_axis", "y_axis" => $height));
        }
        parent::generate($dbData);
        if ($this->children['0']->splitType == 'Stretch' || $this->children['0']->splitType == 'Prevent') {
            JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
        }
    }

}

?>