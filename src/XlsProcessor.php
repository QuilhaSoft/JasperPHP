<?php

namespace JasperPHP;

use \JasperPHP;
use TTransaction;
use PHPExcel;
use PHPExcel_Settings;
use PHPExcel_CachedObjectStorageFactory;
use PHPExcel_Cell_DataType;
use PHPExcel_Style_Alignment;
use PHPExcel_Cell;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;

/**
 * classe XlsProcessor
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 * 
 * 2015.03.11 -- criação
 * */
class XlsProcessor {

    public $wb;
    public $ws;
    public $cols = array();
    public $rows = array();
    private $rowWidthOfSet = 50;
    private $relativex = 0;
    private $relativey = 0;
    static public $rowHeightOfSet = 18;
    static private $rowpos = 1;

    public function __construct(\JasperPHP\Report $jasperObj) {

        $this->jasperObj = $jasperObj;
        $wb = JasperPHP\Instructions::$objOutPut;
        $this->ws = $wb->getActiveSheet(0);
    }

    public static function prepare() {
        $wb = new PHPExcel();
        JasperPHP\Instructions::$objOutPut = $wb;
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array(' memoryCacheSize ' => '8MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
    }

    public static function PageNo() {
        return 0;
    }

    public function MultiCell($arraydata) {
        if ($this->relativey == "")
            $this->relativey = 0;
//$this->mergeCells(    $this->relativex,  ($this->relativey+self::$rowpos),   ($this->cols['c'.($this->mergex+$arraydata['width'])]-1),   ($this->relativey+self::$rowpos)  );
        $x = intval($arraydata['x'] / $this->rowWidthOfSet);
//if($x==0)$x=1 ;
        $y = intval(($arraydata['y'] + ($arraydata['height'] / 2)) / $this->rowHeightOfSet);
        $txt = $arraydata['txt'];
//if($arraydata['pattern']!='')
//   $txt= $this->formatText ($txt, $arraydata['pattern']);
        if ($y > 1)
            self::$rowpos++;
        if ($this->debughtml)
            echo $txt . ",align:" . self::$rowpos . "<br/>";

        $this->setText($x, $y + self::$rowpos, $txt, $arraydata['align'], $arraydata['pattern']);
    }

    /* case "Cell":


      $this->SetText($this->relativex, ($this->relativey+self::$rowpos),$this->analyse_expression($arraydata['txt']),$arraydata['align'], $arraydata['pattern']);
      if($this->debughtml)
      echo  $txt."<br/>";

      break; */

    public function SetY_axis($arraydata) {
        //$y = intval($arraydata['y_axis'] / $this->rowHeightOfSet);
        self::$rowpos ++; /*
          $myx=intval($arraydata['x']);
          $myy=intval($arraydata['y']);
          $this->relativex=$this->cols['c'.$myx];
          $this->relativey=$this->rows['r'.$myy];
          $this->mergex=$myx;
          $this->mergey=$myy;//$arraydata['y']; */
    }

    public function PreventY_axis($arraydata) {
//self::$rowpos++;/*
        $myx = intval($arraydata['x']);
        $myy = intval($arraydata['y']);
        $this->relativex = $this->cols['c' . $myx];
        $this->relativey = $this->rows['r' . $myy];
        $this->mergex = $myx;
        $this->mergey = $myy; //$arraydata['y'];*/
    }

    public function SetXY($arraydata) {
//$y = intval( ($arraydata['y'])/$this->rowHeightOfSet );
//if($y>self::$rowpos)self::$rowpos = intval( ($arraydata['y'])/$this->rowHeightOfSet );
        /* $myx=intval($arraydata['x']);
          $myy=intval($arraydata['y']);
          $this->relativex=$this->cols['c'.$myx];
          $this->relativey=$this->rows['r'.$myy];
          $this->mergex=$myx;
          $this->mergey=$myy;//$arraydata['y']; */
    }

    public function SetFont($arraydata) {
        if ($this->debughtml)
            echo $arraydata['font'] . "," . $arraydata["fontsize"] . "," . $arraydata['fontstyle'] . "<br/>";
        $this->SetFonts($this->relativex, ($this->relativey + self::$rowpos), $arraydata['font'], $arraydata["fontsize"],
                $arraydata['fontstyle']);
//if($this->debughtml)
    }

    public function setText($x, $y, $txt, $align, $pattern) {

        $myformat = '';
//if($this->uselib==0){
//$stlen=strlen($txt);





        if (strpos($pattern, ".") !== false || strpos($pattern, "#") !== false) {
            $this->ws->getCellByColumnAndRow($x, $y)->setValueExplicit($txt, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $this->ws->getStyleByColumnAndRow($x, $y)->getNumberFormat()->setFormatCode($pattern);
        } else {
            $this->ws->getCellByColumnAndRow($x, $y)->setValueExplicit($txt, PHPExcel_Cell_DataType::TYPE_STRING);
        }
        /* if(strpos($pattern,".")!==false || strpos($pattern,"#")!==false){    

          }
          else
          $this->ws->getStyleByColumnAndRow($x, $y)->getNumberFormat()->setFormatCode('@');
         */
//$newstrken=($this->ws->getCellByColumnAndRow($x, $y)->getValue());
//if($this->left($txt,1)=='0' && $stlen>$newstrken){
// for($kkk=0;$kkk<$stlen;$kkk++){
//$myformat.="0";
// echo $myformat.",$txt<br/>";
//  }
//$this->ws->getCellByColumnAndRow($x, $y)->getNumberFormat()->setFormatCode($myformat);
//}
//setCellValueByColumnAndRow($x,$y,$txt);



        if ($align == 'C')
            $this->ws->getStyleByColumnAndRow($x, $y)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        elseif ($align == 'R')
            $this->ws->getStyleByColumnAndRow($x, $y)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        else
            $this->ws->getStyleByColumnAndRow($x, $y)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


        /* }
          else{

          $EXCEL_HALIGN_GENERAL        = 0x00;
          $EXCEL_HALIGN_LEFT            = 0x01;
          $EXCEL_HALIGN_CENTRED        = 0x02;
          $EXCEL_HALIGN_RIGHT            = 0x03;
          $EXCEL_HALIGN_FILLED            = 0x04;
          $EXCEL_HALIGN_JUSITFIED        = 0x05;
          $EXCEL_HALIGN_SEL_CENTRED    = 0x06;    // centred across selection
          $EXCEL_HALIGN_DISTRIBUTED    = 0x07;    //
          if($align=='C')
          $align=$EXCEL_HALIGN_CENTRED;
          elseif($align=='R')
          $align=$EXCEL_HALIGN_RIGHT;
          else
          $align=$EXCEL_HALIGN_LEFT;
          //$this->wfont = new ExcelFont(ExcelFont::WEIGHT_NORMAL);



          $this->wformat->setFont($this->wfont);
          $this->wformat->setAlignment($align);
          if(strpos($pattern,".")!==false || strpos($pattern,"#")!==false){

          $this->wformat->setFormatString($pattern);

          //         $this->wformat->setFont($this->wfont);
          $this->ws->setDouble($x,$y-1,$txt,$this->wformat);
          }
          else{


          $this->ws->setAnsiString($x,$y-1,$txt,$this->wformat); //Mac OSX's iconv not able to convert char * to wchar_t* well.
          }
          if($this->debughtml==true)
          echo "Reset Font/format to default<br/>";
          } */
    }

    public function mergeCells($x1, $y1, $x2, $y2) {
//if($this->uselib==0){
        if ($x2 == "")
            $x2 = $x1;
        if ($y2 == "")
            $y2 = $y1;

        $this->ws->mergeCellsByColumnAndRow($x1, $y1, $x2, $y2);
//}
        /* else{
          if($x2=="")$x2=0;
          if($y2=="")$y2=0;

          $this->ws->mergeCells($x1,$y1-1,($x2-$x1)+1, ($y2-$y1)+1);
          } */
    }

    public function SetFonts($x, $y, $font, $fontsize, $fontstyle) {


//if($this->uselib==0){
//echo "phpexcel";
        $f = $this->ws->getStyleByColumnAndRow($x, $y)->getFont();

        $f->setName($font);

        $f->setSize(intVal($fontsize));

        if (strpos($fontstyle, 'B') !== false)
            $f->setBold(true);
        else
            $f->setBold(false);

//if(strpos($fontstyle,'U')!==false)
//	$f->setUnderline(PHPExcel\PHPExcel_Style_Font::UNDERLINE_SINGLE);
//else
//	$f->setUnderline(PHPExcel_Style_Font::UNDERLINE_NONE);

        if (strpos($fontstyle, 'I') !== false)
            $f->setItalic(true);
        else
            $f->setItalic(false);
    }

    public function deleteEmptyRow() {
        for ($l = 1; $l <= self::$rowpos; $l++) {

            $rh = $this->ws->getRowDimension($l)->getRowHeight();

            if ($rh == 1) {
                $this->ws->removeRow($l, $l + 1);
            }
        }
    }

// print_r($emptrowgroup);
//    public function SetTextColor($x, $y, $cl) {
////if($this->uselib==0){
//        $this->ws->getStyleByColumnAndRow($x, $y)->getFont()->getColor()->setARGB("FF" . $cl);
////}else{
//        /*
//          EGA_BLACK    = 0,    // 000000H
//          EGA_WHITE    = 1,    // FFFFFFH
//          EGA_RED        = 2,    // FF0000H
//          EGA_GREEN    = 3,    // 00FF00H
//          EGA_BLUE    = 4,    // 0000FFH
//          EGA_YELLOW    = 5,    // FFFF00H
//          EGA_MAGENTA    = 6,    // FF00FFH
//          EGA_CYAN    = 7        // 00FFFFH
//         */
////         $this->wfont->setColor(0);
////}
//    }
//    public function SetFillColor($x, $y, $cl) {
//        if ($this->uselib == 0) {
//            $this->ws->getStyleByColumnAndRow($x, $y)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
//            $this->ws->getStyleByColumnAndRow($x, $y)->getFill()->getStartColor()->setARGB('FF' . $cl);
//        } else {
//
//            /*
//              EGA_BLACK    = 0,    // 000000H
//              EGA_WHITE    = 1,    // FFFFFFH
//              EGA_RED        = 2,    // FF0000H
//              EGA_GREEN    = 3,    // 00FF00H
//              EGA_BLUE    = 4,    // 0000FFH
//              EGA_YELLOW    = 5,    // FFFF00H
//              EGA_MAGENTA    = 6,    // FF00FFH
//              EGA_CYAN    = 7        // 00FFFFH
//             */
////     $this->wformat->setBackGround(1);
//        }
//    }
}
