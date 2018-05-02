<?php

namespace JasperPHP;

use \JasperPHP;
use \JasperPHP\Pdf;

/**
 * classe Instruction
 * classe para construção de rótulos de texto
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 * 
 * 2015.03.11 -- criação
 * */
class Instruction {

    private $jasperObj;

    public function __construct(\JasperPHP\Report $jasperObj) {

        $this->jasperObj = $jasperObj;
    }

    public function PreventY_axis($arraydata) {
        //$pdf = \JasperPHP\Pdf;
        $preventY_axis = JasperPHP\Pdf ::$y_axis + $arraydata['y_axis'];
        $pageheight = JasperPHP\Pdf::$arrayPageSetting["pageHeight"];
        $pageFooter = $this->jasperObj->getChildByClassName('PageFooter');
        $pageFooterHeigth = ($pageFooter) ? $pageFooter->children[0]->height : 0;
        $topMargin = JasperPHP\Pdf::$arrayPageSetting["topMargin"];
        $bottomMargin = JasperPHP\Pdf::$arrayPageSetting["bottomMargin"];
        $discount = $pageheight - $pageFooterHeigth - $topMargin - $bottomMargin; //dicount heights of page parts;
        // var_dump($pageFooter);
        //exit;


        if ($preventY_axis >= $discount) {
            if ($pageFooter) {
                $pageFooter->generate(array($this->jasperObj, array('counter' => true)));
            }
            JasperPHP\Pdf::addInstruction(array("type" => "resetY_axis"));
            JasperPHP\Pdf::$currrentPage++;
            JasperPHP\Pdf::addInstruction(array("type" => "AddPage"));
            JasperPHP\Pdf::addInstruction(array("type" => "setPage", "value" => JasperPHP\Pdf::$currrentPage, 'resetMargins' => false));

            JasperPHP\Pdf::runInstructions();
        }
    }

    public function resetY_axis($arraydata) {

        JasperPHP\Pdf::$y_axis = (int) JasperPHP\Pdf::$arrayPageSetting["topMargin"];
    }

    public function SetY_axis($arraydata) {
        if ((JasperPHP\Pdf::$y_axis + $arraydata['y_axis']) <= JasperPHP\Pdf::$arrayPageSetting["pageHeight"]) {
            JasperPHP\Pdf::$y_axis = JasperPHP\Pdf::$y_axis + $arraydata['y_axis'];
        }
    }

    public function ChangeCollumn($arraydata) {
        $pdf = JasperPHP\Pdf;
        if (JasperPHP\Pdf::$arrayPageSetting['columnCount'] > (JasperPHP\Pdf::$arrayPageSetting["CollumnNumber"])) {
            JasperPHP\Pdf::$arrayPageSetting["leftMargin"] = JasperPHP\Pdf::$arrayPageSetting["defaultLeftMargin"] + (JasperPHP\Pdf::$arrayPageSetting["columnWidth"] * JasperPHP\Pdf::$arrayPageSetting["CollumnNumber"]);
            JasperPHP\Pdf::$arrayPageSetting["CollumnNumber"] = JasperPHP\Pdf::$arrayPageSetting['CollumnNumber'] + 1;
        } else {
            JasperPHP\Pdf::$arrayPageSetting["CollumnNumber"] = 1;
            JasperPHP\Pdf::$arrayPageSetting["leftMargin"] = JasperPHP\Pdf::$arrayPageSetting["defaultLeftMargin"];
        }
    }

    public function AddPage($arraydata) {
        // $pdf = JasperPHP\Pdf;
        JasperPHP\Pdf::$pdfOutPut->AddPage();
    }

    public function setPage($arraydata) {
        //$pdf = JasperPHP\Pdf;
        JasperPHP\Pdf::$pdfOutPut->setPage($arraydata["value"], $arraydata["resetMargins"]);
    }

    public function SetFont($arraydata) {
        $arraydata["font"] = strtolower($arraydata["font"]);

        $fontfile = JasperPHP\Pdf::$fontdir . '/' . $arraydata["font"] . '.php';
        // if(file_exists($fontfile) || $this->jasperObj->bypassnofont==false){

        $fontfile = JasperPHP\Pdf::$fontdir . '/' . $arraydata["font"] . '.php';

        JasperPHP\Pdf::$pdfOutPut->SetFont($arraydata["font"], $arraydata["fontstyle"], $arraydata["fontsize"], $fontfile);
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

    public function MultiCell($arraydata) {

        //if($fielddata==true) {
        JasperPHP\Pdf::checkoverflow($arraydata, $arraydata["txt"], null);
        //}
    }

    public function SetXY($arraydata) {
        JasperPHP\Pdf::$pdfOutPut->SetXY($arraydata["x"] + JasperPHP\Pdf::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Pdf::$y_axis);
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
        JasperPHP\Pdf::$pdfOutPut->Rect($arraydata["x"] + JasperPHP\Pdf::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Pdf::$y_axis, $arraydata["width"], $arraydata["height"], $style, $arraydata['border'], $arraydata['fillcolor']);
    }

    public function RoundedRect($arraydata) {
        if ($arraydata['mode'] == 'Transparent')
            $style = '';
        else
            $style = 'FD';
        //
        //        JasperPHP\Pdf::$pdfOutPut->SetLineStyle($arraydata['border']);
        JasperPHP\Pdf::$pdfOutPut->RoundedRect($arraydata["x"] + JasperPHP\Pdf::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Pdf::$y_axis, $arraydata["width"], $arraydata["height"], $arraydata["radius"], '1111', $style, $arraydata['border'], $arraydata['fillcolor']);
    }

    public function Ellipse($arraydata) {
        //JasperPHP\Pdf::$pdfOutPut->SetLineStyle($arraydata['border']);
        JasperPHP\Pdf::$pdfOutPut->Ellipse($arraydata["x"] + $arraydata["width"] / 2 + JasperPHP\Pdf::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Pdf::$y_axis + $arraydata["height"] / 2, $arraydata["width"] / 2, $arraydata["height"] / 2, 0, 0, 360, 'FD', $arraydata['border'], $arraydata['fillcolor']);
    }

    public function Image($arraydata) {
        //echo $arraydata["path"];
        $path = $arraydata["path"];
        $imgtype = mb_substr($path, -3);
        $arraydata["link"] = $arraydata["link"] . "";
        if ($imgtype == 'jpg')
            $imgtype = "JPEG";
        elseif ($imgtype == 'png' || $imgtype == 'PNG')
            $imgtype = "PNG";
        // echo $path;
        if (file_exists($path) || mb_substr($path, 0, 4) == 'http') {
            //echo $path;
            JasperPHP\Pdf::$pdfOutPut->Image($path, $arraydata["x"] + JasperPHP\Pdf::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Pdf::$y_axis, $arraydata["width"], $arraydata["height"], $imgtype, $arraydata["link"]);
        } elseif (mb_substr($path, 0, 21) == "data:image/jpg;base64") {
            $imgtype = "JPEG";
            //echo $path;
            $img = str_replace('data:image/jpg;base64,', '', $path);
            $imgdata = base64_decode($img);
            JasperPHP\Pdf::$pdfOutPut->Image('@' . $imgdata, $arraydata["x"] + JasperPHP\Pdf::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Pdf::$y_axis, $arraydata["width"], $arraydata["height"], '', $arraydata["link"]);
        } elseif (mb_substr($path, 0, 22) == "data:image/png;base64,") {
            $imgtype = "PNG";
            // JasperPHP\Pdf::$pdfOutPut->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $img = str_replace('data:image/png;base64,', '', $path);
            $imgdata = base64_decode($img);


            JasperPHP\Pdf::$pdfOutPut->Image('@' . $imgdata, $arraydata["x"] + JasperPHP\Pdf::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Pdf::$y_axis, $arraydata["width"], $arraydata["height"], '', $arraydata["link"]);
        }
    }

    public function SetTextColor($arraydata) {

        //if($this->jasperObj->hideheader==true && $this->jasperObj->currentband=='pageHeader')
        //    JasperPHP\Pdf::$pdfOutPut->SetTextColor(100,33,30);
        //else
        JasperPHP\Pdf::$pdfOutPut->SetTextColor($arraydata["r"], $arraydata["g"], $arraydata["b"]);
    }

    public function SetDrawColor($arraydata) {
        JasperPHP\Pdf::$pdfOutPut->SetDrawColor($arraydata["r"], $arraydata["g"], $arraydata["b"]);
    }

    public function SetLineWidth($arraydata) {
        JasperPHP\Pdf::$pdfOutPut->SetLineWidth($arraydata["width"]);
    }

    public function breaker($arraydata) {
        JasperPHP\Pdf::print_expression($arraydata);
        if (JasperPHP\Pdf::$print_expression_result == true) {
            if ($pageFooter)
                $pageFooter->generate($this->jasperObj);
            JasperPHP\Pdf::addInstruction(array("type" => "resetY_axis"));
            JasperPHP\Pdf::$currrentPage++;
            JasperPHP\Pdf::addInstruction(array("type" => "AddPage"));
            JasperPHP\Pdf::addInstruction(array("type" => "setPage", "value" => JasperPHP\Pdf::$currrentPage, 'resetMargins' => false));
            JasperPHP\Pdf::runInstructions();
        }
    }

    public function Line($arraydata) {
        JasperPHP\Pdf::print_expression($arraydata);
        if (JasperPHP\Pdf::$print_expression_result == true) {
            JasperPHP\Pdf::$pdfOutPut->Line($arraydata["x1"] + JasperPHP\Pdf::$arrayPageSetting["leftMargin"], $arraydata["y1"] + JasperPHP\Pdf::$y_axis, $arraydata["x2"] + JasperPHP\Pdf::$arrayPageSetting["leftMargin"], $arraydata["y2"] + JasperPHP\Pdf::$y_axis, $arraydata["style"]);
        }
    }

    public function SetFillColor($arraydata) {
        JasperPHP\Pdf::$pdfOutPut->SetFillColor($arraydata["r"], $arraydata["g"], $arraydata["b"]);
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

        $this->showBarcode($arraydata, JasperPHP\Pdf::$y_axis);
    }

    public function CrossTab($arraydata) {

        //$this->generateCrossTab($arraydata, JasperPHP\Pdf::$y_axis);
    }

    public function showBarcode($data, $y) {

        $pdf = JasperPHP\Pdf::get();
        $type = strtoupper($data['barcodetype']);
        $height = $data['height'];
        $width = $data['width'];
        $x = $data['x'];
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
                if ($this->left($code, 3) == "QR:") {

                    $code = $this->right($code, strlen($code) - 3);

                    $pdf->write2DBarcode($code, 'QRCODE', $x, $y, $width, $height, $style, 'N');
                } else
                    $pdf->write2DBarcode($code, 'DATAMATRIX', $x, $y, $width, $height, $style, 'N');
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
        }
    }

}
