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
 * */
class TextField extends Element {

    public function generate($obj = null) {
        $rowData = is_array($obj) ? $obj[1] : null;
        $data = $this->objElement;
        $obj = is_array($obj) ? $obj[0] : $obj;
        $text = (string) $this->objElement->textFieldExpression;
        $arrayText = explode("+", $text);
        $align = "L";
        $fill = 0;
        $border = 0;
        $fontsize = 10;
        $font = "helvetica";
        $rotation = "";
        $fontstyle = "";
        $textcolor = array("r" => 0, "g" => 0, "b" => 0);
        $fillcolor = array("r" => 255, "g" => 255, "b" => 255);
        $stretchoverflow = "false";
        $printoverflow = "false";
        $height = $data->reportElement["height"];
        $drawcolor = array("r" => 0, "g" => 0, "b" => 0);
        $writeHTML = '';
        $isPrintRepeatedValues = '';
        $valign = '';
        if (isset($data->hyperlinkReferenceExpression)) {
            $data->hyperlinkReferenceExpression = $obj->get_expression($data->hyperlinkReferenceExpression,$rowData,false,$this);
        }
        $multiCell = false;
        //SimpleXML object (1 item) [0] // ->codeExpression[0] ->attributes('xsi', true) ->schemaLocation ->attributes('', true) ->type ->drawText ->checksumRequired barbecue:
        //SimpleXMLElement Object ( [@attributes] => Array ( [hyperlinkType] => Reference [hyperlinkTarget] => Blank ) [reportElement] => SimpleX
        //print_r( $data["@attributes"]);
                
        //apply style formatting
        if(isset($data->reportElement['style'])){
        $name = $data->reportElement['style'];
        $obj->applyStyle($name, $data->reportElement, $rowData);
        }

        if (isset($data->reportElement["forecolor"])) {
            $textcolor = array(
                "r" => hexdec(substr($data->reportElement["forecolor"], 1, 2)),
                "g" => hexdec(substr($data->reportElement["forecolor"], 3, 2)),
                "b" => hexdec(substr($data->reportElement["forecolor"], 5, 2))
            );
        }
        if (isset($data->reportElement["backcolor"])) {
            $fillcolor = array(
                "r" => hexdec(substr($data->reportElement["backcolor"], 1, 2)),
                "g" => hexdec(substr($data->reportElement["backcolor"], 3, 2)),
                "b" => hexdec(substr($data->reportElement["backcolor"], 5, 2))
            );
        }
        if ($data->reportElement["mode"] == "Opaque") {
            $fill = 1;
        }
        if ((isset($this->textAdjust) && $this->textAdjust == "StretchHeight") || (isset($this->isStretchWithOverflow) && $this->isStretchWithOverflow == "true")) {
            $stretchoverflow = "true";
        }
        if (isset($data->reportElement["isPrintWhenDetailOverflows"]) && $data->reportElement["isPrintWhenDetailOverflows"] == "true") {
            $printoverflow = "true";
        }
        $box = array();
        if (isset($data->box)) {
            $border = StaticText::formatBox($data->box);
            $box = $data->box;
        }
        if (isset($data->reportElement["key"]) && !empty($data->reportElement["key"])) {
            $height = $fontsize;
        }
        if (isset($data->textElement["textAlignment"])) {
            $align = $this->get_first_value($data->textElement["textAlignment"]);
        }
        if (isset($data->textElement["verticalAlignment"])) {

            $valign = "T";
            if ($data->textElement["verticalAlignment"] == "Bottom") {
                $valign = "B";
            } elseif ($data->textElement["verticalAlignment"] == "Middle") {
                $valign = "C";
            } else {
                $valign = "T";
            }
        }
        if (isset($data->textElement["rotation"])) {
            $rotation = $data->textElement["rotation"];
        }
        if (isset($data->textElement->font["fontName"])) {
            //   $font=$this->recommendFont($data->textFieldExpression,$data->textElement->font["fontName"],$data->textElement->font["pdfFontName"]);
            //$data->textFieldExpression=$font;//$data->textElement->font["pdfFontName"];
            $font = $data->textElement->font["fontName"];
        }
        if (isset($data->textElement->font["size"])) {
            $fontsize = $data->textElement->font["size"];
        }
        if (isset($data->textElement->font["isBold"]) && $data->textElement->font["isBold"] == "true") {
            $fontstyle = $fontstyle . "B";
        }
        if (isset($data->textElement->font["isItalic"]) && $data->textElement->font["isItalic"] == "true") {
            $fontstyle = $fontstyle . "I";
        }
        if (isset($data->textElement->font["isUnderline"]) && $data->textElement->font["isUnderline"] == "true") {
            $fontstyle = $fontstyle . "U";
        }
        $lineHeightRatio = 1;
        if (isset($data->textElement->paragraph["lineSpacing"])) {
            switch ($data->textElement->paragraph["lineSpacing"]) {
                case "1_1_2":
                    $lineHeightRatio = 1.5;
                    break;
                case "Double":
                    $lineHeightRatio = 1.5;
                    break;
                case "Proportional":
                    $lineHeightRatio = $data->textElement->paragraph["lineSpacingSize"];
                    break;
            }
        }
        JasperPHP\Instructions::addInstruction(array(
            "type" => "setCellHeightRatio",
            "ratio" => $lineHeightRatio
        ));

        JasperPHP\Instructions::addInstruction(array(
            "type" => "SetXY",
            "x" => $data->reportElement["x"] + 0,
            "y" => $data->reportElement["y"] + 0,
            "hidden_type" => "SetXY"
        ));
        JasperPHP\Instructions::addInstruction(array(
            "type" => "SetTextColor",
            "forecolor" => $data->reportElement["forecolor"],
            "r" => $textcolor["r"],
            "g" => $textcolor["g"],
            "b" => $textcolor["b"],
            "hidden_type" => "textcolor"
        ));
        JasperPHP\Instructions::addInstruction(array(
            "type" => "SetDrawColor",
            "r" => $drawcolor["r"],
            "g" => $drawcolor["g"],
            "b" => $drawcolor["b"],
            "hidden_type" => "drawcolor"
        ));
        JasperPHP\Instructions::addInstruction(array(
            "type" => "SetFillColor",
            "backcolor" => $data->reportElement["backcolor"] . "",
            "r" => $fillcolor["r"],
            "g" => $fillcolor["g"],
            "b" => $fillcolor["b"],
            "hidden_type" => "fillcolor",
            "fill" => $fill
        ));
        JasperPHP\Instructions::addInstruction(array(
            "type" => "SetFont",
            "font" => $font . "",
            "pdfFontName" => $data->textElement->font? $data->textElement->font["pdfFontName"] . "":"",
            "fontstyle" => $fontstyle . "",
            "fontsize" => $fontsize + 0,
            "hidden_type" => "font"
        ));
        $patternExpression = $this->objElement->patternExpression;
        $writeHTML = false;

        if ($data->textElement['markup'] == 'html') {
            $writeHTML = true;
        }

        switch ($this->objElement->textFieldExpression) {

            case 'new java.util.Date()':
                $text = date("Y-m-d H:i:s");
                break;
            default:
                $text = $obj->get_expression($text,$rowData,$writeHTML,$this);
//                preg_match_all("/P{(\w+)}/", $text, $matchesP);
//                if ($matchesP) {
//                    foreach ($matchesP[1] as $macthP) {
//                        $text = str_ireplace(array('$P{' . $macthP . '}'), array(($obj->arrayParameter[$macthP])), $text);
//                    }
//                }
//                preg_match_all("/V{(\w+)}/", $text, $matchesV);
//                if ($matchesV) {
//                    foreach ($matchesV[1] as $macthV) {
//                        $text = $obj->getValOfVariable($macthV, $text);
//                    }
//                }
//                preg_match_all("/F{[^}]*}/", $text, $matchesF);
//                if ($matchesF) {
//                    foreach ($matchesF[0] as $macthF) {
//                        $macth = str_ireplace(array("F{", "}"), "", $macthF);
//                        $text = $obj->getValOfField($macth, $rowData, $text, $writeHTML);
//                    }
//                }

                break;
        }
        $writeHTML = false;
        if ($data->textElement['markup'] == 'html') {
            $writeHTML = 1;
        } elseif ($data->textElement['markup'] == 'rtf') {
            $multiCell = true;
        } else {
            $text = str_ireplace(array('"+','" +', '+"', '+ "', '"','\n'), array('', '', ''), $text);
        }
        if (isset($data->reportElement["isPrintRepeatedValues"]))
            $isPrintRepeatedValues = $data->reportElement["isPrintRepeatedValues"];

        if ($printoverflow == "true" || $stretchoverflow == "true") {
            $text = str_ireplace(array('+', '+', '"'), array('', '', ''), $text);
        }
        $printWhenExpression = $obj->get_expression($data->reportElement->printWhenExpression, $rowData);
        JasperPHP\Instructions::addInstruction(array("type" => "MultiCell", "width" => $data->reportElement["width"] + 0, "height" => $height + 0, "txt" => $text . "",
            "border" => $border, "align" => $align, "fill" => $fill,
            "hidden_type" => "field", "soverflow" => $stretchoverflow, "poverflow" => $printoverflow,
            "printWhenExpression" => $printWhenExpression . "",
            "link" => $data->hyperlinkReferenceExpression . "", "pattern" => $data["pattern"], "linktarget" => $data["hyperlinkTarget"] . "",
            "writeHTML" => $writeHTML,
            "multiCell" => $multiCell,
            "isPrintRepeatedValues" => $isPrintRepeatedValues,
            "rotation" => $rotation,
            "valign" => $valign,
            "box"=>$box,
            "x" => $data->reportElement["x"] + 0, "y" => $data->reportElement["y"] + 0));

        //$this->checkoverflow($pointer);

        parent::generate($obj);
    }

}
