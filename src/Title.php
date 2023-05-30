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
class Title extends Element {

    public function generate($obj = null) {
        $dbData = $obj->dbData;
        $arrayVariable = ($obj->arrayVariable) ? $obj->arrayVariable : array();
        $recordObject = array_key_exists('recordObj', $arrayVariable) ? $arrayVariable['recordObj']['initialValue'] : "stdClass";
        $row = (is_array($dbData) || $dbData instanceOf \ArrayAccess) ? ( isset($dbData[0]) ? $dbData[0] : array() ) : $obj->rowData;
        
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