<?php

namespace JasperPHP\core;

use JasperPHP\elements\Element;
use JasperPHP\processors\PdfProcessor;

/**
 * Instructions class
 * This class manages and executes instructions for generating Jasper reports.
 */
final class Instructions {

    static public $objOutPut;
    static public $fontdir;
    static private $intructions;
    static public $JasperObj;
    static public $currrentPage = 1;
    static public $y_axis;
    static public $arrayPageSetting;
    static public $print_expression_result;
    static private $instructionProcessor = \JasperPHP\processors\PdfProcessor::class;
    static public $lastPageFooter = true;
    static public $processingPageFooter = false;
    
    private function __construct() {
        
    }

    public static function setProcessor($instructionProcessor) {
        self::$instructionProcessor = $instructionProcessor;
    }

    public static function prepare($report) {
        // Chama o método prepare estático do processador apropriado
        self::$instructionProcessor::prepare($report);
    }

    public static function addInstruction($instruction) {
        self::$intructions[] = $instruction;
    }

    public static function setJasperObj(Element $JasperObj) {
        self::$JasperObj = $JasperObj;
    }

    public static function get() {
        // Para HTML, o objeto real pode ser a própria classe estática ou uma instância armazenada estaticamente
        if (is_callable([self::$instructionProcessor, 'get'])) {
            return self::$instructionProcessor::get();
        }
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
        $JasperObj = self::$JasperObj;
        $instructions = self::$intructions;
        self::$intructions = array();
        
        // Para PDF, uma nova instância é criada por instrução, passando o relatório.
        // Para HTML, usaremos um manipulador de instância estática para manter o estado.
        $isHtml = (self::$instructionProcessor === \JasperPHP\processors\HtmlProcessor::class);
        $instructionHandler = null;
        if (!$isHtml) {
            // Comportamento original para PDF e outros
            foreach ($instructions as $arraydata) {
                $methodName = $arraydata["type"];
                $methodName = $methodName == 'break' ? 'breaker' : $methodName;
                
                $instructionHandler = new self::$instructionProcessor($JasperObj);
                if (method_exists($instructionHandler, $methodName)) {
                    $instructionHandler->$methodName($arraydata);
                }
            }
        } else {
            // Comportamento modificado para HTML para manter o estado
            $instructionHandler = self::$instructionProcessor::getInstance();
            foreach ($instructions as $arraydata) {
                $methodName = $arraydata["type"];
                $methodName = $methodName == 'break' ? 'breaker' : $methodName;

                if (method_exists($instructionHandler, $methodName)) {
                    $instructionHandler->$methodName($arraydata);
                }
            }
        }
    }
}