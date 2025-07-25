<?php

namespace JasperPHP\processors;

use JasperPHP\elements\Report;
use JasperPHP\core\Instructions;
use JasperPHP\database\TLogger;
use JasperPHP\database\TLoggerHTML;
use JasperPHP\elements\Table;

/**
 * PdfProcessor class
 * This class handles PDF generation and processing for Jasper reports.
 */
class PdfProcessor {

    private $jasperObj;
    private $print_expression_result;

    public function __construct(\JasperPHP\elements\Report $jasperObj) {

        $this->jasperObj = $jasperObj;
    }

    public static function prepare($report) {
        Instructions::$arrayPageSetting = $report->arrayPageSetting;
        if ($report->arrayPageSetting["orientation"] == "Landscape") {
            Instructions::$objOutPut = new \TCPDF($report->arrayPageSetting["orientation"], 'pt', array(intval($report->arrayPageSetting["pageHeight"]), intval($report->arrayPageSetting["pageWidth"])), true);
        } else {
            Instructions::$objOutPut = new \TCPDF($report->arrayPageSetting["orientation"], 'pt', array(intval($report->arrayPageSetting["pageWidth"]), intval($report->arrayPageSetting["pageHeight"])), true);
        }
        Instructions::$objOutPut->SetLeftMargin((int) $report->arrayPageSetting["leftMargin"]);
        Instructions::$objOutPut->SetRightMargin((int) $report->arrayPageSetting["rightMargin"]);
        Instructions::$objOutPut->SetTopMargin((int) $report->arrayPageSetting["topMargin"]);
        Instructions::$objOutPut->SetAutoPageBreak(true, (int) $report->arrayPageSetting["bottomMargin"] / 2);
        //self::$pdfOutPut->AliasNumPage();
        Instructions::$objOutPut->setPrintHeader(false);
        Instructions::$objOutPut->setPrintFooter(false);
        Instructions::$objOutPut->AddPage();
        Instructions::$objOutPut->setPage(1, true);
        Instructions::$y_axis = (int) $report->arrayPageSetting["topMargin"];

        if (Instructions::$fontdir == "")
            Instructions::$fontdir = dirname(__FILE__) . "/tcpdf/fonts";
    }

    public static function PageNo() {
        Instructions::$objOutPut->PageNo();
    }

    public static function get() {
        return Instructions::$objOutPut;
    }

    public function PreventY_axis($arraydata) {
        //$pdf = \JasperPHP\Pdf;
        $preventY_axis = Instructions::$y_axis + (int)$arraydata['y_axis'];
        $pageheight = Instructions::$arrayPageSetting["pageHeight"];
        $pageFooter = $this->jasperObj->getChildByClassName('PageFooter');
        $pageFooterHeigth = ($pageFooter) ? $pageFooter->children[0]->height : 0;
        $topMargin = Instructions::$arrayPageSetting["topMargin"];
        $bottomMargin = Instructions::$arrayPageSetting["bottomMargin"];
        $discount = $pageheight - $pageFooterHeigth - $topMargin - $bottomMargin; //dicount heights of page parts;
        // var_dump($pageFooter);
        //exit;


        if ($preventY_axis >= $discount) {
            if ($pageFooter) {
                Instructions::$lastPageFooter = false;
                $pageFooter->generate($this->jasperObj);
            }
            Instructions::addInstruction(array("type" => "resetY_axis"));
            Instructions::$currrentPage++;
            Instructions::addInstruction(array("type" => "AddPage"));
            Instructions::addInstruction(array("type" => "setPage", "value" => Instructions::$currrentPage, 'resetMargins' => false));
            Instructions::runInstructions();
            $pageHeader = $this->jasperObj->getChildByClassName('PageHeader');
            if ($pageHeader)
                $pageHeader->generate($this->jasperObj);
            //repeat column header?
            if ($this->jasperObj::$columnHeaderRepeat){
				 $columnHeader = $this->jasperObj->getChildByClassName('ColumnHeader');
				 if($columnHeader)
					$columnHeader->generate($this->jasperObj);
                }
            Instructions::runInstructions();
        }
    }

    public function resetY_axis($arraydata) {

        Instructions::$y_axis = (int) Instructions::$arrayPageSetting["topMargin"];
    }

    public function SetY_axis($arraydata) {
        if ((Instructions::$y_axis + (int)$arraydata['y_axis']) <= Instructions::$arrayPageSetting["pageHeight"]) {
            Instructions::$y_axis = Instructions::$y_axis + (int)$arraydata['y_axis'];
        }
    }

    public function ChangeCollumn($arraydata) {
        $pdf = Instructions::get();
        if (Instructions::$arrayPageSetting['columnCount'] > (Instructions::$arrayPageSetting["CollumnNumber"])) {
            Instructions::$arrayPageSetting["leftMargin"] = Instructions::$arrayPageSetting["defaultLeftMargin"] + (Instructions::$arrayPageSetting["columnWidth"] * Instructions::$arrayPageSetting["CollumnNumber"]);
            Instructions::$arrayPageSetting["CollumnNumber"] = Instructions::$arrayPageSetting['CollumnNumber'] + 1;
        } else {
            Instructions::$arrayPageSetting["CollumnNumber"] = 1;
            Instructions::$arrayPageSetting["leftMargin"] = Instructions::$arrayPageSetting["defaultLeftMargin"];
        }
    }

    public function AddPage($arraydata) {
        $this->jasperObj->pageChanged = true;

        // $pdf = JasperPHP\Pdf;
        Instructions::$objOutPut->AddPage();
    }

    public function setPage($arraydata) {
        //$pdf = JasperPHP\Pdf;
        Instructions::$objOutPut->setPage($arraydata["value"], $arraydata["resetMargins"]);
    }

    public function SetFont($arraydata) {
        $arraydata["font"] = strtolower($arraydata["font"]);

        $fontfile = Instructions::$fontdir . '/' . $arraydata["font"] . '.php';
        // if(file_exists($fontfile) || $this->jasperObj->bypassnofont==false){

        $fontfile = Instructions::$fontdir . '/' . $arraydata["font"] . '.php';

        Instructions::$objOutPut->SetFont($arraydata["font"], $arraydata["fontstyle"], $arraydata["fontsize"], $fontfile);
        /* }
          else{
          $arraydata["font"]="freeserif";
          if($arraydata["fontstyle"]=="")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserif',$arraydata["fontstyle"],$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserif.php');
          elseif($arraydata["fontstyle"]=="B")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifb',$arraydata["fontstyle"],$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifb.php');
          elseif($arraydata["fontstyle"]=="I")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifi',$arraydata["fontstyle"],$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifi.php');
          elseif($arraydata["fontstyle"]=="BI")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifbi',$arraydata["fontstyle"],$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifbi.php');
          elseif($arraydata["fontstyle"]=="BIU")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifbi',"BIU",$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifbi.php');
          elseif($arraydata["fontstyle"]=="U")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserif',"U",$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserif.php');
          elseif($arraydata["fontstyle"]=="BU")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifb',"U",$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifb.php');
          elseif($arraydata["fontstyle"]=="IU")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifi',"IU",$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifbi.php');


          } */
    }

    public function SetCellHeightRatio($arraydata) {
        Instructions::$objOutPut->SetCellHeightRatio($arraydata["ratio"]);
    }
    
    public function MultiCell($arraydata) {

        //if($fielddata==true) {
        $this->checkoverflow($arraydata);
        //}
    }

    public function SetXY($arraydata) {
        Instructions::$objOutPut->SetXY($arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis);
    }

    public function Cell($arraydata) {
        //                print_r($arraydata);
        //              echo "<br/>";
        //JasperPHP\Pdf::$pdfOutPut->Cell($arraydata["width"], $arraydata["height"], $this->jasperObj->updatePageNo($arraydata["txt"]), $arraydata["border"], $arraydata["ln"], $arraydata["align"], $arraydata["fill"], $arraydata["link"] . "", 0, true, "T", $arraydata["valign"]);
    }

    public function Rect($arraydata) {
        if ($arraydata['mode'] == 'Transparent')
            $style = '';
        else
            $style = 'FD';
        //      JasperPHP\Pdf::$pdfOutPut->SetLineStyle($arraydata['border']);
        Instructions::$objOutPut->Rect($arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis, $arraydata["width"], $arraydata["height"], $style, $arraydata['border'], $arraydata['fillcolor']);
    }

    public function RoundedRect($arraydata) {
        if ($arraydata['mode'] == 'Transparent')
            $style = '';
        else
            $style = 'FD';
       
        //
        //        JasperPHP\Pdf::$pdfOutPut->SetLineStyle($arraydata['border']);
        Instructions::$objOutPut->RoundedRect($arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis, $arraydata["width"], $arraydata["height"], $arraydata["radius"], '1111', $style, $arraydata['border'], $arraydata['fillcolor']);
    	//draw only border
        if(isset($arraydata['border']['width']) && $arraydata['border']['width']>0){		
            Instructions::$objOutPut->SetLineStyle($arraydata['border']);
            $arraydata['border']['color'] = '000';
        //    dd($arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis, $arraydata["width"], $arraydata["height"], $arraydata["radius"], '1111',$arraydata['border']);
            Instructions::$objOutPut->RoundedRect($arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis, $arraydata["width"], $arraydata["height"], $arraydata["radius"], '1111',$style,$arraydata['border'], $arraydata['fillcolor']);		
            Instructions::$objOutPut->SetLineStyle(array());
        }
    }

    public function Ellipse($arraydata) {
        //JasperPHP\Pdf::$pdfOutPut->SetLineStyle($arraydata['border']);
        Instructions::$objOutPut->Ellipse($arraydata["x"] + $arraydata["width"] / 2 + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis + $arraydata["height"] / 2, $arraydata["width"] / 2, $arraydata["height"] / 2, 0, 0, 360, 'FD', $arraydata['border'], $arraydata['fillcolor']);
    }

    public function Image($arraydata) {
        //echo $arraydata["path"];
        $this->print_expression($arraydata);
        if ($this->print_expression_result == true) {

            $path = $arraydata["path"];
            $imgtype = mb_substr($path, -3);
            $arraydata["link"] = $arraydata["link"] . "";
            if ($imgtype == 'jpg')
                $imgtype = "JPEG";
            elseif ($imgtype == 'png' || $imgtype == 'PNG')
                $imgtype = "PNG";
            // echo $path;
            $imagePath = str_replace(array('"', '\\', '/'), array('', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $path);
            //not full patch?
            if (!file_exists($imagePath)) {
                $imagePath = getcwd() . DIRECTORY_SEPARATOR . $imagePath;
            }
            if (file_exists($imagePath)) {

                //echo $imagePath;
                //exit;
                Instructions::$objOutPut->Image($imagePath, $arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis, $arraydata["width"], $arraydata["height"], $imgtype, $arraydata["link"], '', false, 300, '', false, false, $arraydata["border"], $arraydata["fitbox"]);
            } elseif (mb_substr($path, 0, 4) == 'http') {
                // echo $path;
                ///exit;
                Instructions::$objOutPut->Image($path, $arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis, $arraydata["width"], $arraydata["height"], $imgtype, $arraydata["link"], '', false, 300, '', false, false, $arraydata["border"], $arraydata["fitbox"]);
            } elseif (mb_substr($path, 0, 21) == "data:image/jpg;base64") {
                $imgtype = "JPEG";
                //echo $path;
                $img = str_replace('data:image/jpg;base64,', '', $path);
                $imgdata = base64_decode($img);
                Instructions::$objOutPut->Image('@' . $imgdata, $arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis, $arraydata["width"], $arraydata["height"], '', '', '', false, 300, '', false, false, $arraydata["border"], $arraydata["fitbox"]);
            } elseif (mb_substr($path, 0, 22) == "data:image/png;base64,") {
                $imgtype = "PNG";
                // JasperPHP\Pdf::$pdfOutPut->setImageScale(PDF_IMAGE_SCALE_RATIO);

                $img = str_replace('data:image/png;base64,', '', $path);
                $imgdata = base64_decode($img);


                Instructions::$objOutPut->Image('@' . $imgdata, $arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis, $arraydata["width"], $arraydata["height"], '', $arraydata["link"], '', false, 300, '', false, false, 0, $arraydata["fitbox"]);
            }
        }
    }

    public function SetTextColor($arraydata) {

        //if($this->jasperObj->hideheader==true && $this->jasperObj->currentband=='pageHeader')
        //    JasperPHP\Pdf::$pdfOutPut->SetTextColor(100,33,30);
        //else
        Instructions::$objOutPut->SetTextColor($arraydata["r"], $arraydata["g"], $arraydata["b"]);
    }

    public function SetDrawColor($arraydata) {
        Instructions::$objOutPut->SetDrawColor($arraydata["r"], $arraydata["g"], $arraydata["b"]);
    }

    public function SetLineWidth($arraydata) {
        Instructions::$objOutPut->SetLineWidth($arraydata["width"]);
    }

    public function breaker($arraydata) {
        $this->print_expression($arraydata);
        $pageFooter = $this->jasperObj->getChildByClassName('PageFooter');
        if ($this->print_expression_result == true) {
            if ($pageFooter)
                $pageFooter->generate($this->jasperObj);
            Instructions::addInstruction(array("type" => "resetY_axis"));
            Instructions::$currrentPage++;
            Instructions::addInstruction(array("type" => "AddPage"));
            Instructions::addInstruction(array("type" => "setPage", "value" => Instructions::$currrentPage, 'resetMargins' => false));
            $pageHeader = $this->jasperObj->getChildByClassName('PageHeader');
            //if (JasperPHP\Pdf::$print_expression_result == true) {
            if ($pageHeader)
                $pageHeader->generate($this->jasperObj);
            //}
            Instructions::runInstructions();
        }
    }

    public function Line($arraydata) {
        $this->print_expression($arraydata);
        if ($this->print_expression_result == true) {
           //var_dump($arraydata["style"]);
            //echo ($arraydata["x1"] + Instructions::$arrayPageSetting["leftMargin"])."||". ($arraydata["y1"] + Instructions::$y_axis)."||". ($arraydata["x2"] + Instructions::$arrayPageSetting["leftMargin"])."||". $arraydata["y2"] + Instructions::$y_axis."||". $arraydata["style"]; 
            
            Instructions::$objOutPut->Line((int)$arraydata["x1"] + Instructions::$arrayPageSetting["leftMargin"], (int)$arraydata["y1"] + Instructions::$y_axis, (int)$arraydata["x2"] + Instructions::$arrayPageSetting["leftMargin"], (int)$arraydata["y2"] + Instructions::$y_axis, $arraydata["style"]);
        }
    }

    public function SetFillColor($arraydata) {
        Instructions::$objOutPut->SetFillColor($arraydata["r"], $arraydata["g"], $arraydata["b"]);
    }

    public function lineChart($arraydata) {

        // $this->generateLineChart($arraydata, JasperPHP\Pdf::$y_axis);
    }

    public function barChart($arraydata) {

        // $this->generateBarChart($arraydata, JasperPHP\Pdf::$y_axis, 'barChart');
    }

    public function pieChart($arraydata) {

        //$this->generatePieChart($arraydata, JasperPHP\Pdf::$y_axis);
    }

    public function stackedBarChart($arraydata) {

        //$this->generateBarChart($arraydata, JasperPHP\Pdf::$y_axis, 'stackedBarChart');
    }

    public function stackedAreaChart($arraydata) {

        //$this->generateAreaChart($arraydata, JasperPHP\Pdf::$y_axis, $arraydata["type"]);
    }

    public function Barcode($arraydata) {

        $this->showBarcode($arraydata, Instructions::$y_axis);
    }

    public function CrossTab($arraydata) {

        //$this->generateCrossTab($arraydata, JasperPHP\Pdf::$y_axis);
    }
 
    public function Table($arraydata){
	$arraydata['tableElement']->render($arraydata);		
    }
	
    public function showBarcode($data, $y) {

        $pdf = Instructions::get();
        $type = strtoupper($data['barcodetype']);
        $height = $data['height'];
        $width = $data['width'];
        $x = $data['x'] + Instructions::$arrayPageSetting["leftMargin"];
        $y = $data['y'] + $y;
        $textposition = $data['textposition'];
        $code = $data['code'];
        //$code=$this->analyse_expression($code);
        $modulewidth = $data['modulewidth'];
        if ($textposition == "" || $textposition == "none")
            $withtext = false;
        else
            $withtext = true;

        $style = array(
            'border' => false,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'text' => $withtext,
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );


        //[2D barcode section]
        //DATAMATRIX
        //QRCODE,H or Q or M or L (H=high level correction, L=low level correction)
        // -------------------------------------------------------------------
        // PDF417 (ISO/IEC 15438:2006)

        /*

          The $type parameter can be simple 'PDF417' or 'PDF417' followed by a
          number of comma-separated options:

          'PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6'

          Possible options are:

          a  = aspect ratio (width/height);
          e  = error correction level (0-8);

          Macro Control Block options:

          t  = total number of macro segments;
          s  = macro segment index (0-99998);
          f  = file ID;
          o0 = File Name (text);
          o1 = Segment Count (numeric);
          o2 = Time Stamp (numeric);
          o3 = Sender (text);
          o4 = Addressee (text);
          o5 = File Size (numeric);
          o6 = Checksum (numeric).

          Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional.
          To use a comma character ',' on text options, replace it with the character 255: "\xff".

         */
        switch ($type) {
            case "PDF417":
                $pdf->write2DBarcode($code, 'PDF417', $x, $y, $width, $height, $style, 'N');
                break;
            case "DATAMATRIX":

                //$this->pdf->Cell( $width,10,$code);
                //echo $this->left($code,3);
                if (substr($code, 0, 3) == "QR:") {

                    $code = substr($code, 3);

                    $pdf->write2DBarcode($code, 'QRCODE', $x, $y, $width, $height, $style, 'N');
                } else
                    $pdf->write2DBarcode($code, 'DATAMATRIX', $x, $y, $width, $height, $style, 'N');
                break;
            case "QRCODE":
                $pdf->write2DBarcode($code, 'QRCODE', $x, $y, $width, $height, $style, 'N');
                break;
            case "CODE128":

                $pdf->write1DBarcode($code, 'C128', $x, $y, $width, $height, $modulewidth, $style, 'N');

                // $this->pdf->write1DBarcode($code, 'C128', $x, $y, $width, $height,"", $style, 'N');
                break;
            case "EAN8":
                $pdf->write1DBarcode($code, 'EAN8', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
            case "EAN13":
                $pdf->write1DBarcode($code, 'EAN13', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
            case "CODE39":
                $pdf->write1DBarcode($code, 'C39', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
            case "CODE93":
                $pdf->write1DBarcode($code, 'C93', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
            case "I25":
            case "INT2OF5":
            case "INTERLEAVED2OF5":
                $pdf->write1DBarcode($code, 'I25', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
            case "POSTNET":
                $pdf->write1DBarcode($code, 'POSTNET', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
        }
    }

    public function checkoverflow($obj) {
        $maxheight = 0;
        /* @var \TCPDF $pdf */
        $pdf = Instructions::$objOutPut;
        $JasperObj = $this->jasperObj;
        // var_dump($obj->children);
        $txt = (string) $obj['txt'];
        //$newfont = $JasperObj->recommendFont($txt, null, null);
        //$pdf->SetFont($newfont,$pdf->getFontStyle(),$this->defaultFontSize);
        $this->print_expression($obj);
        $arraydata = $obj;

        $pdf->SetXY($arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + Instructions::$y_axis);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        //default
        $pLeft=1;
        $pTop=0;
        $pRight=1;
        $pBottom=0;         
        //suport padding cells
        if(isset($obj['box']) && !empty($obj['box'])){   
            if(isset($obj['box']['leftPadding'])){
            $pLeft=$obj['box']['leftPadding'];            
            }
            if(isset($obj['box']['topPadding'])){
            $pTop=$obj['box']['topPadding'];
            }
            if(isset($obj['box']['rightPadding'])){
            $pRight=$obj['box']['rightPadding'];            
            }
            if(isset($obj['box']['bottomPadding'])){
            $pBottom = $obj['box']['bottomPadding'];            
            }
        }
        $pdf->setCellPaddings($pLeft, $pTop, $pRight, $pBottom);
        $w = $arraydata["width"];
        $h = $arraydata["height"];
        $pdf->StartTransform();

        $clipx = $arraydata["x"] + Instructions::$arrayPageSetting["leftMargin"];
        $clipy = $arraydata["y"] + Instructions::$y_axis;
        $clipw = $arraydata["width"];
        $cliph = $arraydata["height"];

        $rotated = false;
        if ($this->print_expression_result == true) {
            $angle = $this->rotate($arraydata);
            if ($angle != 0) {
                $pdf->Rect($clipx, $clipy, $clipw, $cliph, 'CNZ');
                $pdf->Rotate($angle);
                $rotated = true;
                switch ($angle) {
                    case 90:
                        $x = $x - $arraydata["height"];
                        $h = $arraydata["width"];
                        $w = $arraydata["height"];
                        break;
                    case 180:
                        $x = $x - $arraydata["width"];
                        $y = $y - $arraydata["height"];
                        break;
                    case 270:
                        $y = $y - $arraydata["width"];
                        $h = $arraydata["width"];
                        $w = $arraydata["height"];
                        break;
                }
            }
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
                if ($arraydata["valign"] == "C")
                    $arraydata["valign"] = "M";
                if ($arraydata["valign"] == "")
                    $arraydata["valign"] = "T";

                // clip width & height
                if (!$rotated) {
                    $pdf->Rect($clipx, $clipy, $clipw, $cliph, 'CNZ');
                }
                
                $pattern = (array_key_exists("pattern", $arraydata)) ? $arraydata["pattern"] : '';
                $text = $pattern != '' ? $JasperObj->formatText($txt, $pattern) : $txt;
                $pdf->MultiCell(
                    $w, $h, $text, $arraydata["border"], $arraydata["align"], $arraydata["fill"], 0, $x, $y, true, 0, false, true, $h, $arraydata["valign"]);
                if (isset($arraydata["link"]) && !empty($arraydata["link"])) {
                    $pdf->Link($x, $y, $arraydata["width"], $arraydata["height"], $arraydata["link"]);
                }
            } elseif ($arraydata["poverflow"] == "true" || $arraydata["soverflow"] == "true") {
                if ($arraydata["valign"] == "C")
                    $arraydata["valign"] = "M";
                if ($arraydata["valign"] == "")
                    $arraydata["valign"] = "T";

                $x = $pdf->GetX();
                $yAfter = $pdf->GetY();
                $maxheight = array_key_exists('maxheight', $arraydata) ? $arraydata['maxheight'] : 0;
                //if($arraydata["link"])   echo $arraydata["linktarget"].",".$arraydata["link"]."<br/><br/>";
                $pdf->MultiCell($w, $h, $JasperObj->formatText($txt, $arraydata["pattern"]), $arraydata["border"]
                        , $arraydata["align"], $arraydata["fill"], 1, $x, $y, true, 0, false, true, $maxheight); //,$arraydata["valign"]);
                if (($yAfter + $arraydata["height"]) <= Instructions::$arrayPageSetting["pageHeight"]) {
                    Instructions::$y_axis = $pdf->GetY() - 20;
                }
            } else {
                $pdf->MultiCell($w, $h, $JasperObj->formatText($txt, $arraydata["pattern"]), $arraydata["border"], $arraydata["align"], $arraydata["fill"], 1, $x, $y, true, 0, true, true, $maxheight);
            }
            $pdf->StopTransform();
            
        }
    }

    public function print_expression($data) {
        $expression = $data["printWhenExpression"];
        $this->print_expression_result = false;
        if ($expression != "") {
            //echo      'if('.$expression.'){$this->print_expression_result=true;}';
            $expression = $this->jasperObj->get_expression($expression, $this->jasperObj->rowData);
            
            // WARNING: Using eval() can be a security risk and makes debugging difficult.
            // A more robust solution would involve parsing and evaluating expressions without eval.
            $oldErrorReporting = error_reporting(0); // Temporarily disable error reporting
            try {
                // Adicionando log para depuração
                $logger  = new \JasperPHP\database\TLoggerHTML('debug_eval.log'); // Certifique-se de que TLoggerHTML está disponível
                $logger->write("Expressão antes do eval: " . $expression);
                eval('if(' . $expression . '){$this->print_expression_result=true;}');
            } catch (\ParseError $e) {
                $this->jasperObj->addDebugMessage("Erro de Parse na expressão (PdfProcessor): " . $expression . " - " . $e->getMessage());
            } finally {
                error_reporting($oldErrorReporting); // Restore original error reporting
            }
            
        } else
            $this->print_expression_result = true;
    }

    public function rotate($arraydata) {
        $pdf = Instructions::$objOutPut;
        if (array_key_exists("rotation", $arraydata)) {
            $type = (string) $arraydata["rotation"];
            $angle = null;
            if ($type == "")
                $angle = 0;
            elseif ($type == "Left")
                $angle = 90;
            elseif ($type == "Right")
                $angle = 270;
            elseif ($type == "UpsideDown")
                $angle = 180;

            return $angle;
        }
    }

}
