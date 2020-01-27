<?php

namespace JasperPHP;

use \JasperPHP;

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
class PageHeader extends Element {

    public function generate($obj = null) {
        $rowData = $obj->rowData;
        $data = $this->objElement;
        $obj = is_array($obj) ? $obj[0] : $obj;
        $height = (string) $this->children['0']->objElement['height'];
        parent::generate(array($obj, $rowData));

        JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
    }
}