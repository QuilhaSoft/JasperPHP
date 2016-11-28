<?php
namespace JasperPHP;
use JasperPHP;
/**
* classe TLabel
* classe para construção de rótulos de texto
*
* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
* @version  2015.03.11
* @access   restrict
* 
* 2015.03.11 -- criação
**/
class ComponentElement extends Element
{


    public function generate($obj = null)
    {
        $data = $this->objElement;
        $rowData = is_array($obj)?$obj[1]:null; 
        $obj = is_array($obj)?$obj[0]:$obj; 
        $x=$data->reportElement["x"];
        $y=$data->reportElement["y"];
        $width=$data->reportElement["width"];
        $height=$data->reportElement["height"];

        
        //simplexml_tree( $data);        
        // echo "<br/><br/>";
        //simplexml_tree( $data->children('jr',true));
        //echo "<br/><br/>";
        //SimpleXML object (1 item) [0] // ->codeExpression[0] ->attributes('xsi', true) ->schemaLocation ->attributes('', true) ->type ->drawText ->checksumRequired barbecue: 
        foreach($data->children('jr',true) as $barcodetype =>$content){
            $text = $content->codeExpression;

            preg_match_all("/P{(\w+)}/",$text ,$matchesP);
            if($matchesP){
                foreach($matchesP[1] as $macthP){
                    $text = str_ireplace(array('$P{'.$macthP.'}'),array(($obj->arrayParameter[$macthP])),$text); 
                } 
            }
            preg_match_all("/V{(\w+)}/",$text ,$matchesV);
            if($matchesV){
                foreach($matchesV[1] as $macthV){
                    $text = $obj->getValOfVariable($macthV,$text); 
                }

            }
            preg_match_all("/F{(\w+)}/",$text ,$matchesF);
            if($matchesF){
                foreach($matchesF[1] as $macthF){
                    $text = $obj->getValOfField($macthF,$rowData,$text);
                }
            }

            $barcodemethod="";
            $textposition="";
            if($barcodetype=="barbecue"){
                $barcodemethod=$data->children('jr',true)->attributes('', true) ->type;
                $textposition="";
                $checksum=$data->children('jr',true)->attributes('', true) ->checksumRequired;
                $code=$text;
                if($content->attributes('', true) ->drawText=='true')
                    $textposition="bottom";

                $modulewidth=$content->attributes('', true) ->moduleWidth;

            }else{

                $barcodemethod=$barcodetype;
                $textposition=$content->attributes('', true)->textPosition;
                //$data->children('jr',true)->textPosition;
                //$content['textPosition'];
                $code=$text;
                $modulewidth=$content->attributes('', true)->moduleWidth;



            }
            if($modulewidth=="")
                $modulewidth=0.4;
            //                            echo "Barcode: $code,position: $textposition <br/><br/>";
            JasperPHP\Pdf::addInstruction(array("type"=>"Barcode","barcodetype"=>$barcodemethod,"x"=>$x,"y"=>$y,"width"=>$width,"height"=>$height,'textposition'=>$textposition,'code'=>$code,'modulewidth'=>$modulewidth));

            /*
            <jr:barbecue xmlns:jr="http://jasperreports.sourceforge.net/jasperreports/components" 
            * xsi:schemaLocation="http://jasperreports.sourceforge.net/jasperreports/components http://jasperreports.sourceforge.net/xsd/components.xsd" 
            * type="2of7" drawText="false" checksumRequired="false">
            <jr:codeExpression><![CDATA["1234"]]></jr:codeExpression>
            </jr:barbecue>
            * <jr:Code128 xmlns:jr="http://jasperreports.sourceforge.net/jasperreports/components" 
            * xsi:schemaLocation="http://jasperreports.sourceforge.net/jasperreports/components http://jasperreports.sourceforge.net/xsd/components.xsd"
            *  textPosition="bottom">
            <jr:codeExpression><![CDATA[]]></jr:codeExpression>
            </jr:Code128>
            */


        }
    }
}
?>