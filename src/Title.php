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
        $rowIndex = 0;
        $row = ( is_array($dbData) ) ? (array_key_exists($rowIndex, $dbData)) ? $dbData[$roIndex] : null : $obj->rowData;
        //$obj->rowData = $row;
        if ($row) {
            switch ($row) {
                case (is_object($row)):
                    $rowArray = get_object_vars($row);
                    break;
                case (method_exists($row, 'toArray')):
                    $rowArray = $row->toArray();
                    break;
                default:
                    $rowArray = array();
                    break;
            }
        } else {
            $rowArray = array();
        }
        foreach ($this->children as $child) {
            // se for objeto
            if (is_object($child)) {
                $dataAndParameters = array_merge($_POST, $rowArray);
                $height = (string) $this->children['0']->objElement['height'];
                parent::generate(array($obj, $dataAndParameters));
                JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
            }
        }
    }

}

?>