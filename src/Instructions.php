<?php

namespace JasperPHP;

use \JasperPHP;

/*
 * classe Instructions
 * 
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
 * */

final class Instructions {

    static public $objOutPut;
    static public $fontdir;
    static private $intructions;
    static public $JasperObj;
    static public $currrentPage = 1;
    static public $y_axis;
    static public $arrayPageSetting;
    static public $print_expression_result;
    static private $instructionProcessor = '\JasperPHP\PdfProcessor';
    static public $lastPageFooter = true;
    static public $processingPageFooter = false;
    

    /*
     * método __construct()
     * não existirão instâncias de TConnection, por isto estamos marcando-o como private
     */

    private function __construct() {
        
    }

    public static function setProcessor($instructionProcessor) {
        self::$instructionProcessor = $instructionProcessor;
    }

    public static function prepare($report) {
        self::$instructionProcessor::prepare($report);
    }

    public static function addInstruction($instruction) {
        self::$intructions[] = $instruction;
    }

    public static function setJasperObj(JasperPHP\Element $JasperObj) {
        self::$JasperObj = $JasperObj;
    }

    public static function get() {
        return self::$objOutPut;
    }

    public static function getInstructions() {
        return self::$intructions;
    }

    public static function clearInstructrions() {
        self::$intructions = array();
    }

    public static function getPageNo() {
        return self::$objOutPut->PageNo();
    }

    public static function runInstructions() {
        $pdf = self::$objOutPut;
        $JasperObj = self::$JasperObj;
        $instructions = self::$intructions;
        //var_dump($instructions);
        self::$intructions = array();
        //$maxheight = null;
        foreach ($instructions as $arraydata) {
            $methodName = $arraydata["type"];
            $methodName = $methodName == 'break' ? 'breaker' : $methodName;

            //$instructionProcessorClass = '\JasperPHP\/' + ;
            $instruction = new self::$instructionProcessor($JasperObj);
            if (method_exists($instruction, $methodName)) {
                $instruction->$methodName($arraydata);
            }
        }
    }

}
