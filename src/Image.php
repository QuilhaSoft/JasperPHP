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

        $rowData = is_array($obj)?$obj[1]:null;
        $data = $this->objElement;
        $obj = is_array($obj)?$obj[0]:$obj; 
        $text=$data->imageExpression;
        //echo $imagepath;
        //echo $imagepath;
        //$text= substr($data->imageExpression, 1, -1);
        preg_match_all("/P{(\w+)}/",$text ,$matchesP);
        if($matchesP){
            foreach($matchesP[1] as $macthP){
                $text = str_ireplace(array('$P{'.$macthP.'}'),array(utf8_encode($obj->arrayParameter[$macthP])),$text); 
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