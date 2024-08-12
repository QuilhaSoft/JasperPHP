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
class PageFooter extends Element {

    public function generate($obj = null) {
        $dbData = $obj->dbData;
        $arrayVariable = ($obj->arrayVariable) ? $obj->arrayVariable : array();
        $recordObject = array_key_exists('recordObj', $arrayVariable) ? $arrayVariable['recordObj']['initialValue'] : "stdClass";
        // $rowIndex = 0;
        $row = (is_array($dbData) || $dbData instanceOf \ArrayAccess) ? $dbData[0] : $obj->rowData;
        //$row = ( is_array($dbData) ) ? (array_key_exists($rowIndex, $dbData)) ? $dbData[$rowIndex] : array() : $obj->lastRowData;
        if (!$row) {
            $row = array();
        }
        
        $height = (string) $this->children['0']->objElement['height'];
        JasperPHP\Instructions::addInstruction(array("type" => "resetY_axis"));
        JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => ($obj->arrayPageSetting["pageHeight"] - $obj->arrayPageSetting["topMargin"] - $this->children['0']->height - $obj->arrayPageSetting["bottomMargin"])));
        JasperPHP\Instructions::$processingPageFooter = true;
        parent::generate(array($obj, $row));
        JasperPHP\Instructions::$processingPageFooter = false;
        
        JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $height));
    }

}
