<?php

namespace JasperPHP;

use \JasperPHP;
use \TCPDF;

/*
 * classe Pdf
 * 
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
 * */

final class Pdf {

    static public $pdfOutPut;
    static public $fontdir;
    static private $intructions;
    static public $JasperObj;
    static public $print_expression_result;
    static public $currrentPage = 1;
    static public $y_axis;
    static public $arrayPageSetting;

    /*
     * método __construct()
     * não existirão instâncias de TConnection, por isto estamos marcando-o como private
     */

    private function __construct() {
        
    }

    public static function prepare($report) {
        self::$arrayPageSetting = $report->arrayPageSetting;
        if ($report->arrayPageSetting["orientation"] == "Landscape") {
            self::$pdfOutPut = new TCPDF($report->arrayPageSetting["orientation"], 'pt', array(intval($report->arrayPageSetting["pageHeight"]), intval($report->arrayPageSetting["pageWidth"])), true);
        } else {
            self::$pdfOutPut = new TCPDF($report->arrayPageSetting["orientation"], 'pt', array(intval($report->arrayPageSetting["pageWidth"]), intval($report->arrayPageSetting["pageHeight"])), true);
        }
        self::$pdfOutPut->SetLeftMargin((int) $report->arrayPageSetting["leftMargin"]);
        self::$pdfOutPut->SetRightMargin((int) $report->arrayPageSetting["rightMargin"]);
        self::$pdfOutPut->SetTopMargin((int) $report->arrayPageSetting["topMargin"]);
        self::$pdfOutPut->SetAutoPageBreak(true, (int) $report->arrayPageSetting["bottomMargin"] / 2);
        //self::$pdfOutPut->AliasNumPage();
        self::$pdfOutPut->setPrintHeader(false);
        self::$pdfOutPut->setPrintFooter(false);
        self::$pdfOutPut->AddPage();
        self::$pdfOutPut->setPage(1, true);
        self::$y_axis = (int)$report->arrayPageSetting["topMargin"];

        if (self::$fontdir == "")
            self::$fontdir = dirname(__FILE__) . "/tcpdf/fonts";
    }

    public static function addInstruction($instruction) {
        self::$intructions[] = $instruction;
    }

    public static function setJasperObj(JasperPHP\Element $JasperObj) {
        self::$JasperObj = $JasperObj;
    }

    public static function get() {
        return self::$pdfOutPut;
    }

    public static function getInstructions() {
        return self::$intructions;
    }

    public static function clearInstructrions() {
        self::$intructions = array();
    }

    public static function getPageNo() {
        return self::$pdfOutPut->PageNo();
    }

    public static function runInstructions() {
        $pdf = self::$pdfOutPut;
        $JasperObj = self::$JasperObj;
        $instructions = self::$intructions;
        self::$intructions = array();
        //$maxheight = null;
        $instruction = new \JasperPHP\Instruction($JasperObj);
        foreach ($instructions as $arraydata) {
            //self::rotate($arraydata["rotation"]);
            if (array_key_exists("rotation", $arraydata)) {
                if ($arraydata["rotation"] == "Left") {
                    $w = $arraydata["width"];
                    $arraydata["width"] = $arraydata["height"];
                    $arraydata["height"] = $w;
                    $pdf->SetXY($pdf->GetX() - $arraydata["width"], $pdf->GetY());
                } elseif ($arraydata["rotation"] == "Right") {
                    $w = $arraydata["width"];
                    $arraydata["width"] = $arraydata["height"];
                    $arraydata["height"] = $w;
                    $pdf->SetXY($pdf->GetX(), $pdf->GetY() - $arraydata["height"]);
                } elseif ($arraydata["rotation"] == "UpsideDown") {
                    //soverflow"=>$stretchoverflow,"poverflow"
                    $arraydata["soverflow"] = true;
                    $arraydata["poverflow"] = true;
                    //   $w=$arraydata["width"];
                    // $arraydata["width"]=$arraydata["height"];
                    //$arraydata["height"]=$w;
                    $pdf->SetXY($pdf->GetX() - $arraydata["width"], $pdf->GetY() - $arraydata["height"]);
                }
            }

            $methodName = $arraydata["type"];
            $methodName = $methodName == 'break'? 'breaker':$methodName;
            if (method_exists($instruction, $methodName)) {
                $instruction->$methodName($arraydata);
            }
        }
    }

    public static function checkoverflow($obj) {
        $pdf = self::$pdfOutPut;
        $JasperObj = self::$JasperObj;
        // var_dump($obj->children); 
        $txt = (string) $obj['txt'];
        //$newfont = $JasperObj->recommendFont($txt, null, null);
        //$pdf->SetFont($newfont,$pdf->getFontStyle(),$this->defaultFontSize);
        self::print_expression($obj);
        $arraydata = $obj;
        $pdf->SetXY($arraydata["x"] + self::$arrayPageSetting["leftMargin"], $arraydata["y"] + self::$y_axis);
        if (self::$print_expression_result == true) {
            // echo $arraydata["link"];
            if ($arraydata["link"]) {
                //print_r($arraydata);
                //$this->debughyperlink=true;
                //  echo $arraydata["link"].",print:".$this->print_expression_result;
                //$arraydata["link"] = $JasperObj->analyse_expression($arraydata["link"], "");
                //$this->debughyperlink=false;
            }
            //print_r($arraydata);


            if ($arraydata["writeHTML"] == true) {
                //echo  ($txt);
                $pdf->writeHTML($txt, true, 0, true, true);
                $pdf->Ln();
                /* if($this->currentband=='detail'){
                  if($this->maxpagey['page_'.($pdf->getPage()-1)]=='')
                  $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                  else{
                  if($this->maxpagey['page_'.($pdf->getPage()-1)]<$pdf->GetY())
                  $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                  }
                  } */
            } elseif ($arraydata["poverflow"] == "false" && $arraydata["soverflow"] == "false") {
                if ($arraydata["valign"] == "M")
                    $arraydata["valign"] = "C";
                if ($arraydata["valign"] == "")
                    $arraydata["valign"] = "T";

                // $text = $txt[0];
                while ($pdf->GetStringWidth((mb_convert_encoding($txt, 'utf-8'))) > $arraydata["width"]) { // aka a gambiarra da gambiarra funcionan assim nao mude a naão ser que de problema seu bosta
                    if ($txt != $pdf->getAliasNbPages() && $txt != ' ' . $pdf->getAliasNbPages()) {
                        $txt = mb_substr($txt, 0, -1);
                    }
                }

                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pattern = (array_key_exists("pattern", $arraydata)) ? $arraydata["pattern"] : '';
                $text = $pattern != '' ? $JasperObj->formatText($txt, $pattern) : $txt;
                $pdf->Cell($arraydata["width"], $arraydata["height"], $text, $arraydata["border"], "", $arraydata["align"], $arraydata["fill"], $arraydata["link"], 0, true, "T", $arraydata["valign"]);
            } elseif ($arraydata["poverflow"] == "true") {
                if ($arraydata["valign"] == "C")
                    $arraydata["valign"] = "M";
                if ($arraydata["valign"] == "")
                    $arraydata["valign"] = "T";

                $x = $pdf->GetX();
                $yAfter = $pdf->GetY();
                $maxheight = array_key_exists('maxheight', $arraydata) ? $arraydata['maxheight'] : '';
                //if($arraydata["link"])   echo $arraydata["linktarget"].",".$arraydata["link"]."<br/><br/>";
                $pdf->MultiCell($arraydata["width"], $arraydata["height"], $JasperObj->formatText($txt, $arraydata["pattern"]), $arraydata["border"]
                        , $arraydata["align"], $arraydata["fill"], 1, '', '', true, 0, false, true, $maxheight); //,$arraydata["valign"]);
                if (($yAfter + $arraydata["height"]) <= self::$arrayPageSetting["pageHeight"]) {
                    self::$y_axis = $pdf->GetY() - 20;
                }
            } elseif ($arraydata["soverflow"] == "true") {

                if ($arraydata["valign"] == "M")
                    $arraydata["valign"] = "C";
                if ($arraydata["valign"] == "")
                    $arraydata["valign"] = "T";

                $pdf->Cell($arraydata["width"], $arraydata["height"], $JasperObj->formatText($txt, $arraydata["pattern"]), $arraydata["border"], "", $arraydata["align"], $arraydata["fill"], $arraydata["link"] . "", 0, true, "T", $arraydata["valign"]);
                $pdf->Ln();
            }
            else {
                $pdf->MultiCell($arraydata["width"], $arraydata["height"], $JasperObj->formatText($txt, $arraydata["pattern"]), $arraydata["border"], $arraydata["align"], $arraydata["fill"], 1, '', '', true, 0, true, true, $maxheight);
            }
        }
        //$JasperObj->print_expression_result = false;
    }
    public static function rotate($type, $x = -1, $y = -1) {
        $pdf = self::$pdfOutPut;
        if ($type == "")
            $angle = 0;
        elseif ($type == "Left")
            $angle = 90;
        elseif ($type == "Right")
            $angle = 270;
        elseif ($type == "UpsideDown")
            $angle = 180;

        if ($x == -1)
            $x = $pdf->getX();
        if ($y == -1)
            $y = $pdf->getY();
        if ($this->angle != 0)
            $pdf->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $pdf->k;
            $cy = ($pdf->h - $y) * $pdf->k;
            $pdf->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    public static function print_expression($data) {
        $expression = $data["printWhenExpression"];
        self::$print_expression_result = false;
        if ($expression != "") {
            //echo      'if('.$expression.'){$this->print_expression_result=true;}';
            //$expression=$this->analyse_expression($expression);
            error_reporting(0);
            eval('if(' . $expression . '){self::$print_expression_result=true;}');
            error_reporting(5);
        } else
            self::$print_expression_result = true;
    }


}
