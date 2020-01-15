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
class Image extends Element
{


    public function generate($obj = null)
    {

        $row = is_array($obj)?$obj[1]:null;
        $data = $this->objElement;
        $obj = is_array($obj)?$obj[0]:$obj; 
        $text=$data->imageExpression;
        //echo $imagepath;
        //echo $imagepath;
        //$text= substr($data->imageExpression, 1, -1);
        $text = $obj->get_expression($row, $row);

        $imagetype= substr($text,-3);
        //$data->hyperlinkReferenceExpression=$this->analyse_expression($data->hyperlinkReferenceExpression);
        $data->hyperlinkReferenceExpression=trim(str_replace(array('"',''),"",$data->hyperlinkReferenceExpression));
        // echo $text; 
        switch($data['scaleImage']) {
            case "FillFrame":
                JasperPHP\Pdf::addInstruction(array("type"=>"Image","path"=>$text,"x"=>$data->reportElement["x"]+0,"y"=>$data->reportElement["y"]+0,"width"=>$data->reportElement["width"]+0,
                    "height"=>$data->reportElement["height"]+0,"imgtype"=>$imagetype,"link"=>$data->hyperlinkReferenceExpression,
                    "hidden_type"=>"image","linktarget"=>$data["hyperlinkTarget"].""));
                break;
            default:
                JasperPHP\Pdf::addInstruction(array("type"=>"Image","path"=>$text,"x"=>$data->reportElement["x"]+0,"y"=>$data->reportElement["y"]+0,"width"=>$data->reportElement["width"]+0,
                    "height"=>$data->reportElement["height"]+0,"imgtype"=>$imagetype,"link"=>$data->hyperlinkReferenceExpression,
                    "hidden_type"=>"image","linktarget"=>$data["hyperlinkTarget"].""));
                break;
        }


    }
}
?>