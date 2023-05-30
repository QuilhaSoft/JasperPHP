<?php

namespace JasperPHP;
use \JasperPHP;
use PDO;
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
class Title extends Element {

    public function generate($obj = null) {
        $row = $obj->rowData;
        
        if (!$row) {
            $row = array();
        }
        
        foreach ($this->children as $child) {
            // se for objeto
            if (is_object($child)) {
                $height = (string) $this->children['0']->objElement['height'];
                parent::generate(array($obj, $row));
                JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
            }
        }
    }

}

?>