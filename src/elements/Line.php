<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * Line class
 * This class represents a line element in a Jasper report.
 */
class Line extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        $data = $this->objElement;
        $rowData = $this->report->rowData;

        $print_expression_result = false;
        $printWhenExpression = (string) $data->reportElement->printWhenExpression;
        if ($printWhenExpression != '') {
            $printWhenExpressionEvaluated = $this->report->get_expression($printWhenExpression, $rowData);
            eval('if(' . $printWhenExpressionEvaluated . '){$print_expression_result=true;}');
        } else {
            $print_expression_result = true;
        }

        if (!$print_expression_result) {
            parent::generate();
            return;
        }

        $drawcolor = ["r" => 0, "g" => 0, "b" => 0];
        if (isset($data->reportElement["forecolor"])) {
            $drawcolor = [
                "r" => hexdec(substr($data->reportElement["forecolor"], 1, 2)),
                "g" => hexdec(substr($data->reportElement["forecolor"], 3, 2)),
                "b" => hexdec(substr($data->reportElement["forecolor"], 5, 2))
            ];
        }

        $linewidth = 0;
        $dash = '';
        if (isset($data->graphicElement->pen)) {
            $pen = $data->graphicElement->pen;
            $linewidth = (float) ($pen["lineWidth"] ?? 0);
            if (isset($pen["lineStyle"])) {
                if ($pen["lineStyle"] == "Dotted") $dash = "0,1";
                elseif ($pen["lineStyle"] == "Dashed") $dash = "4,2";
            }
        }

        $hidden_type = "line";
        if (isset($data->reportElement['positionType']) && $data->reportElement['positionType'] == "FixRelativeToBottom") {
            $hidden_type = "relativebottomline";
        }

        $style = ['color' => $drawcolor, 'width' => $linewidth, 'dash' => $dash];
        
        $x = (int) $data->reportElement["x"];
        $y = (int) $data->reportElement["y"];
        $width = (int) $data->reportElement["width"];
        $height = (int) $data->reportElement["height"];

        $lineInstruction = [
            "type" => "Line",
            "hidden_type" => $hidden_type,
            "style" => $style,
            "forecolor" => (string) ($data->reportElement["forecolor"] ?? '#000000'),
            "printWhenExpression" => $printWhenExpression
        ];

        if ($width > $height) { // Horizontal line
            $lineInstruction["x1"] = $x;
            $lineInstruction["y1"] = $y;
            $lineInstruction["x2"] = $x + $width;
            $lineInstruction["y2"] = $y + $height - 1;
        } else { // Vertical line
            $lineInstruction["x1"] = $x;
            $lineInstruction["y1"] = $y;
            $lineInstruction["x2"] = $x + $width - 1;
            $lineInstruction["y2"] = $y + $height;
        }
        
        Instructions::addInstruction($lineInstruction);
        Instructions::addInstruction(["type" => "SetDrawColor", "r" => 0, "g" => 0, "b" => 0, "hidden_type" => "drawcolor"]);
        Instructions::addInstruction(["type" => "SetFillColor", "r" => 255, "g" => 255, "b" => 255, "hidden_type" => "fillcolor"]);
        
        parent::generate();
    }
}

?>