<?php

namespace JasperPHP;

use \JasperPHP;

/**
 * classe TLabel
 * classe para construção de rótulos de texto
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 * 
 * 2015.03.11 -- criação
 * */
class Rectangle extends Element {

    public function generate($obj = null) {

        $rowData = is_array($obj) ? $obj[1] : null;
        $data = $this->objElement;
        $radius = $data['radius'] + 0;
        $mode = $data->reportElement["mode"] . "";
        $drawcolor = array("r" => 0, "g" => 0, "b" => 0);
        // if($data['mode']=='Opaque')
        //   $fillcolor=array("r"=>255,"g"=>255,"b"=>255);
        $borderwidth = 1;

        if (isset($data->graphicElement->pen["lineWidth"]))
            $borderwidth = $data->graphicElement->pen["lineWidth"];

        if (isset($data->graphicElement->pen["lineColor"]))
            $drawcolor = array("r" => hexdec(substr($data->graphicElement->pen["lineColor"], 1, 2)), "g" => hexdec(substr($data->graphicElement->pen["lineColor"], 3, 2)), "b" => hexdec(substr($data->graphicElement->pen["lineColor"], 5, 2)));

        $dash = "";
        // dd($data);;
        if ($data->graphicElement->pen["lineStyle"] == "Dotted")
            $dash = "0,1";
        elseif ($data->graphicElement->pen["lineStyle"] == "Dashed")
            $dash = "4,2";
        elseif ($data->graphicElement->pen["lineStyle"] == "Solid")
            $dash = "";
//echo "$borderwidth,";

        $border = array('width' => $borderwidth, 'color' => $drawcolor, 'cap' => 'square',
            'join' => 'miter', 'dash' => $dash);


        //array($borderset=>array('width'=>$data->box->pen["lineWidth"],
        //(butt, round, square),'join'=>'miter' (miter, round,bevel),
        //'dash'=>2 ("2,1","2"),
        //  'colour'=>array(110,20,30)  ));
        //&&$data->box->pen["lineWidth"]>0
        //border can be array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
        //elseif()


        if (isset($data->reportElement["forecolor"])) {
            $drawcolor = array("r" => hexdec(substr($data->reportElement["forecolor"], 1, 2)), "g" => hexdec(substr($data->reportElement["forecolor"], 3, 2)), "b" => hexdec(substr($data->reportElement["forecolor"], 5, 2)));
        }

        if (isset($data->reportElement["backcolor"]) && ($mode == 'Opaque' || $mode == '')) {

            $fillcolor = array("r" => hexdec(substr($data->reportElement["backcolor"], 1, 2)), "g" => hexdec(substr($data->reportElement["backcolor"], 3, 2)), "b" => hexdec(substr($data->reportElement["backcolor"], 5, 2)));
        } else
            $fillcolor = array("r" => 255, "g" => 255, "b" => 255);


        //$this->pointer[]=array("type"=>"SetDrawColor","r"=>$drawcolor["r"],"g"=>$drawcolor["g"],"b"=>$drawcolor["b"],"hidden_type"=>"drawcolor");
        // $this->pointer[]=array("type"=>"SetFillColor","r"=>$fillcolor["r"],"g"=>$fillcolor["g"],"b"=>$fillcolor["b"],"hidden_type"=>"fillcolor");
//       if($radius=='')
//        $this->pointer[]=array("type"=>"Rect","x"=>$data->reportElement["x"]+0,"y"=>$data->reportElement["y"]+0,"width"=>$data->reportElement["width"]+0,
//                "height"=>$data->reportElement["height"]+0,"hidden_type"=>"rect",
//                "fillcolor"=>$fillcolor."","mode"=>$data->reportElement["mode"]."",'border'=>0);
//        else
        JasperPHP\Instructions::addInstruction(array("type" => "RoundedRect", "x" => $data->reportElement["x"] + 0,
            "y" => $data->reportElement["y"] + 0, "width" => $data->reportElement["width"] + 0,
            "height" => $data->reportElement["height"] + 0, "hidden_type" => "roundedrect", "radius" => $radius,
            "fillcolor" => $fillcolor,
            "mode" => $mode, 'border' => $border));
    }

}

?>