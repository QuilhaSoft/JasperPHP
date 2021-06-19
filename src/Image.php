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
        $text = $obj->get_expression($text, $row);
        $text = str_ireplace(array('"+','" +', '+"', '+ "', '"'), array('', '', ''), $text);

        $imagetype= substr($text,-3);
        //$data->hyperlinkReferenceExpression=$this->analyse_expression($data->hyperlinkReferenceExpression);
        $data->hyperlinkReferenceExpression=trim(str_replace(array('"',''),"",$data->hyperlinkReferenceExpression));
            // echo $text;

        $arraydata = [
            "type" => "Image",
            "path" => $text,
            "x" => $data->reportElement["x"] + 0,
            "y" => $data->reportElement["y"] + 0,
            "width" => $data->reportElement["width"] + 0,
            "height" => $data->reportElement["height"] + 0,
            "imgtype" => $imagetype,
            "link" => $data->hyperlinkReferenceExpression,
            "hidden_type" => "image",
            "linktarget" => $data["hyperlinkTarget"] . "",
            "border" => 0,
            "fitbox" => false
        ];
        if (isset($data->box)) {
            $arraydata["border"] = StaticText::formatBox($data->box);
        }
        switch ($data['scaleImage']) {
            case "FillFrame":
                break;
            default:
                switch ($data['hAlign']) {
                    case "Center":
                        $arraydata["fitbox"] = "C";
                        break;
                    case "Right":
                        $arraydata["fitbox"] = "R";
                        break;
                    default: // "Left"
                        $arraydata["fitbox"] = "L";
                        break;
                }
                switch ($data['vAlign']) {
                    case "Middle":
                        $arraydata["fitbox"] .= "M";
                        break;
                    case "Bottom":
                        $arraydata["fitbox"] .= "B";
                        break;
                    default: // "Top"
                        $arraydata["fitbox"] .= "T";
                        break;
                }
        }
        JasperPHP\Instructions::addInstruction($arraydata);
        

    }
}
?>
