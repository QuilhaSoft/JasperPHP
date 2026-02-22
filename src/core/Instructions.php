<?php

namespace JasperPHP\core;

use JasperPHP\elements\Element;
use JasperPHP\processors\PdfProcessor;

/**
 * Instructions class
 * This class manages and executes instructions for generating Jasper reports.
 */
final class Instructions
{

    static public $objOutPut;
    static public $fontdir;
    static private $intructions = array();
    static public $JasperObj;
    static private $jasperObjStack = array();
    static public $currrentPage = 1;
    static public $y_axis;
    static public $arrayPageSetting;
    static public $print_expression_result;
    static private $instructionProcessor = \JasperPHP\processors\PdfProcessor::class;
    static public $lastPageFooter = true;
    static public $processingPageFooter = false;

    public static function reset()
    {
        self::$objOutPut = null;
        self::$intructions = array();
        self::$JasperObj = null;
        self::$jasperObjStack = array();
        self::$currrentPage = 1;
        self::$y_axis = null;
        self::$arrayPageSetting = null;
        self::$print_expression_result = null;
        self::$lastPageFooter = true;
        self::$processingPageFooter = false;

        if (method_exists(self::$instructionProcessor, 'reset')) {
            self::$instructionProcessor::reset();
        }
        // Keep $fontdir and $instructionProcessor as they are likely constant or set once
    }

    private function __construct() {}

    public static function setProcessor($instructionProcessor)
    {
        self::$instructionProcessor = $instructionProcessor;
    }

    public static function prepare($report)
    {
        // Chama o método prepare estático do processador apropriado
        self::$instructionProcessor::prepare($report);
    }

    public static function addInstruction($instruction)
    {
        self::$intructions[] = $instruction;
    }

    public static function setJasperObj(Element $JasperObj)
    {
        if (self::$JasperObj !== null) {
            array_push(self::$jasperObjStack, self::$JasperObj);
        }
        self::$JasperObj = $JasperObj;
    }

    public static function restoreJasperObj()
    {
        if (!empty(self::$jasperObjStack)) {
            self::$JasperObj = array_pop(self::$jasperObjStack);
        }
    }

    public static function get()
    {
        // Para HTML, o objeto real pode ser a própria classe estática ou uma instância armazenada estaticamente
        if (is_callable([self::$instructionProcessor, 'get'])) {
            return self::$instructionProcessor::get();
        }
        return self::$objOutPut;
    }

    public static function getInstructions()
    {
        return self::$intructions;
    }

    public static function clearInstructrions()
    {
        self::$intructions = array();
    }

    public static function getPageNo()
    {
        return self::$objOutPut->PageNo();
    }

    public static function runInstructions()
    {
        $JasperObj = self::$JasperObj;
        $instructions = self::$intructions;
        self::$intructions = array();

        $instructionHandler = null;
        if (method_exists(self::$instructionProcessor, 'getInstance')) {
            $instructionHandler = self::$instructionProcessor::getInstance($JasperObj);
        }

        foreach ($instructions as $arraydata) {
            $methodName = $arraydata["type"];
            $methodName = $methodName == 'break' ? 'breaker' : $methodName;

            if ($instructionHandler) {
                if (method_exists($instructionHandler, $methodName)) {
                    $instructionHandler->$methodName($arraydata);
                }
            } else {
                // Fallback for processors without getInstance (re-instantiated per instruction)
                $handler = new self::$instructionProcessor($JasperObj);
                if (method_exists($handler, $methodName)) {
                    $handler->$methodName($arraydata);
                }
            }
        }
    }
}
