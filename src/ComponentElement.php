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

        //table =========================================
		$jrs = $data->children('jr',true);	
		if(isset($jrs->table)){
		$table = new JasperPHP\Table($jrs->table);
		$table->generate(array($obj,$rowData,$data->reportElement));
		}//end table
        
        //simplexml_tree( $data);        
        // echo "<br/><br/>";
        //simplexml_tree( $data->children('jr',true));
        //echo "<br/><br/>";
        //SimpleXML object (1 item) [0] // ->codeExpression[0] ->attributes('xsi', true) ->schemaLocation ->attributes('', true) ->type ->drawText ->checksumRequired barbecue: 
        foreach($data->children('jr',true) as $barcodetype =>$content){
            $text = $obj->get_expression($content->codeExpression,$rowData,false,$this);
            
            $barcodemethod="";
            $textposition="";
            if($barcodetype=="barbecue"){
                $barcodemethod=$data->children('jr',true)->attributes('', true) ->type;
                //var_dump($barcodemethod);
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
                $modulewidth=1;
            //                            echo "Barcode: $code,position: $textposition <br/><br/>";
            JasperPHP\Instructions::addInstruction(array("type"=>"Barcode","barcodetype"=>$barcodemethod,"x"=>$x,"y"=>$y,"width"=>$width,"height"=>$height,'textposition'=>$textposition,'code'=>$code,'modulewidth'=>$modulewidth));



        }
    }
}
?>
