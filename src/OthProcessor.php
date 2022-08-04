<?php

namespace JasperPHP;

use \JasperPHP;
use TTransaction;

/**
 * classe Processor
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 * 
 * 2015.03.11 -- create
 * */
class OthProcessor {

    private $jasperObj;
    private $print_expression_result;

    /*
     * method __construct()
     * @ \JasperPHP\Report $jasperObj
     */

    public function __construct(\JasperPHP\Report $jasperObj) {

        $this->jasperObj = $jasperObj;
    }

    /*
     * method prepare()
     * instanciate document class, prepare a document, set margins etc
     * @ \JasperPHP\Report $jasperObj
     */

    public static function prepare(\JasperPHP\Report $jasperObj) {
        
    }

    /*
     * method get()
     * 
     */

    public static function get() {
        JasperPHP\Instructions::$objOutPut;
    }

    /*
     * method PageNo()
     * get the actual page number
     */

    public static function PageNo() {
        
    }

    /*
     * method MultiCell()
     * create a text element
     * @param = $arraydata
     */

    public function MultiCell($arraydata) {
        
    }

    /*
     * method SetY_axis()
     * set y axis in document
     * @param = $arraydata
     */

    public function SetY_axis($arraydata) {
        
    }

    /*
     * method PreventY_axis()
     * prevenmt out off page data and create a new page
     * @param = $arraydata
     */

    public function PreventY_axis($arraydata) {
        
    }

    /*
     * method SetXY()
     * set X and Y position of cursor into a band
     * @param = $arraydata
     */

    public function SetXY($arraydata) {
        
    }

    /*
     * method ChangeCollumn()
     * change collumn
     * @param = $arraydata
     */

    public function ChangeCollumn($arraydata) {
        
    }

    /*
     * method AddPage()
     * add page collumn
     * @param = $arraydata
     */

    public function AddPage($arraydata) {
        // $pdf = JasperPHP\Pdf;
        JasperPHP\Instructions::$objOutPut->AddPage();
    }

    /*
     * method setPage()
     * set a active page in pdf processor
     * @param = $arraydata
     */

    public function setPage($arraydata) {
        //$pdf = JasperPHP\Pdf;
        JasperPHP\Instructions::$objOutPut->setPage($arraydata["value"], $arraydata["resetMargins"]);
    }

    /*
     * method SetFont()
     * set font, unused at this time
     * @param = $arraydata
     */

    public function SetFont($arraydata) {
        
    }

    /*
     * method Rect()
     * create a rectangle
     * @param = $arraydata
     */

    public function Rect($arraydata) {
        
    }

    /*
     * method RoundedRect()
     * create a rounded rectangle
     * @param = $arraydata
     */

    public function RoundedRect($arraydata) {
        
    }

    /*
     * method Ellipse()
     * create a Ellipse
     * @param = $arraydata
     */

    public function Ellipse($arraydata) {
        
    }

    /*
     * method Image()
     * insert an image into document
     * @param = $arraydata
     */

    public function Image($arraydata) {
        
    }

    /*
     * method SetTextColor()
     * ...
     * @param = $arraydata
     */

    public function SetTextColor($arraydata) {
        
    }

    /*
     * method SetDrawColor()
     * ...
     * @param = $arraydata
     */

    public function SetDrawColor($arraydata) {
        
    }

    /*
     * method SetLineWidth()
     * ...
     * @param = $arraydata
     */

    public function SetLineWidth($arraydata) {
        
    }

    /*
     * method breaker()
     * create a breake page
     * @param = $arraydata
     */

    public function breaker($arraydata) {
        
    }

    /*
     * method Line()
     * create a line
     * @param = $arraydata
     */

    public function Line($arraydata) {
        
    }

    /*
     * method SetFillColor()
     * set a fill color of rect, roundrect, elipse objects
     * @param = $arraydata
     */

    public function SetFillColor($arraydata) {
        
    }

    /*
     * method lineChart()
     * unused at this time
     * @param = $arraydata
     */

    public function lineChart($arraydata) {
        
    }

    /*
     * method barChart()
     * unused at this time
     * @param = $arraydata
     */

    public function barChart($arraydata) {
        
    }

    /*
     * method pieChart()
     * unused at this time
     * @param = $arraydata
     */

    public function pieChart($arraydata) {
        
    }

    /*
     * method stackedBarChart()
     * unused at this time
     * @param = $arraydata
     */

    public function stackedBarChart($arraydata) {
        
    }

    /*
     * method stackedAreaChart()
     * unused at this time
     * @param = $arraydata
     */

    public function stackedAreaChart($arraydata) {
        
    }

    /*
     * method Barcode()
     * create a bar code object
     * @param = $arraydata
     */

    public function Barcode($arraydata) {

        $this->showBarcode($arraydata, JasperPHP\Instructions::$y_axis);
    }

    /*
     * method CrossTab()
     * unused at this time
     * @param = $arraydata
     */

    public function CrossTab($arraydata) {
        
    }

    /*
     * method showBarcode()
     * show a barcode object
     * @param = $data
     * @param = $y
     */

    public function showBarcode($data, $y) {
        
    }

    /*
     * method checkoverflow()
     * check if data into textbox and statictext overflow a width of object
     * @param = $obj
     */

    public function checkoverflow($obj) {
        
    }

    /*
     * method print_expression()
     * evaluate printWhenExpression end set $this->print_expression_result
     * @param = $data
     */

    public function print_expression($data) {
        
    }

    /*
     * method rotate()
     * rotate a text into textbox and statictext objects
     * @param = $arraydata
     */

    public function rotate($arraydata) {
        
    }

}
