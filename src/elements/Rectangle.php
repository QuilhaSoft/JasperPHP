<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * Rectangle class
 * This class represents a rectangle element in a Jasper report.
 */
class Rectangle extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        $data = $this->objElement;
        $radius = (float) ($data['radius'] ?? 0);
        $mode = (string) ($data->reportElement["mode"] ?? 'Transparent');

        $drawcolor = ["r" => 0, "g" => 0, "b" => 0];
        $borderwidth = 0;
        $dash = '';

        if (isset($data->graphicElement->pen)) {
            $pen = $data->graphicElement->pen;
            $borderwidth = (float) ($pen["lineWidth"] ?? 0);

            if (isset($pen["lineColor"])) {
                $drawcolor = [
                    "r" => hexdec(substr($pen["lineColor"], 1, 2)),
                    "g" => hexdec(substr($pen["lineColor"], 3, 2)),
                    "b" => hexdec(substr($pen["lineColor"], 5, 2))
                ];
            }

            $lineStyle = (string) ($pen["lineStyle"] ?? "Solid");
            if ($lineStyle == "Dotted") $dash = "0,1";
            elseif ($lineStyle == "Dashed") $dash = "4,2";
        }

        if (isset($data->reportElement["forecolor"])) {
            $drawcolor = [
                "r" => hexdec(substr($data->reportElement["forecolor"], 1, 2)),
                "g" => hexdec(substr($data->reportElement["forecolor"], 3, 2)),
                "b" => hexdec(substr($data->reportElement["forecolor"], 5, 2))
            ];
        }

        $fillcolor = null;
        if ($mode == 'Opaque' && isset($data->reportElement["backcolor"])) {
            $fillcolor = [
                "r" => hexdec(substr($data->reportElement["backcolor"], 1, 2)),
                "g" => hexdec(substr($data->reportElement["backcolor"], 3, 2)),
                "b" => hexdec(substr($data->reportElement["backcolor"], 5, 2))
            ];
        }

        $border = [
            'width' => $borderwidth,
            'color' => $drawcolor,
            'cap' => 'square',
            'join' => 'miter',
            'dash' => $dash
        ];

        Instructions::addInstruction([
            "type" => "RoundedRect",
            "x" => (int) $data->reportElement["x"],
            "y" => (int) $data->reportElement["y"],
            "width" => (int) $data->reportElement["width"],
            "height" => (int) $data->reportElement["height"],
            "hidden_type" => "roundedrect",
            "radius" => $radius,
            "fillcolor" => $fillcolor,
            "mode" => $mode,
            'border' => $border
        ]);
    }
}

?>
