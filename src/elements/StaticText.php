<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * StaticText class
 * This class represents a static text element in a Jasper report.
 */
class StaticText extends Element
{
    public $adjust = 1;

    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        $data = $this->objElement;
        $rowData = $this->report->rowData;

        $printWhenExpression = (string) ($data->reportElement->printWhenExpression ?? '');
        if ($printWhenExpression !== '') {
            $printWhenExpressionEvaluated = $this->report->get_expression($printWhenExpression, $rowData);
            $print_expression_result = false;
            eval('if(' . $printWhenExpressionEvaluated . '){$print_expression_result=true;}');
            if (!$print_expression_result) {
                return;
            }
        }

        $text = (string) $data->text;
        $height = (int) $data->reportElement["height"];
        $rotation = (string) ($data->textElement["rotation"] ?? '');
        $align = $this->get_first_value($data->textElement["textAlignment"] ?? 'L');
        $valign = $this->getVerticalAlignment($data->textElement["verticalAlignment"] ?? 'T');
        
        $font = 'helvetica';
        $fontstyle = '';
        $fontsize = 10;
        if (isset($data->textElement->font)) {
            $fontElement = $data->textElement->font;
            $font = $this->recommendFont($text, (string)($fontElement["fontName"] ?? ''), (string)($fontElement["pdfFontName"] ?? ''));
            $fontsize = (int) ($fontElement["size"] ?? 10);
            if ((string)($fontElement["isBold"] ?? 'false') == "true") $fontstyle .= "B";
            if ((string)($fontElement["isItalic"] ?? 'false') == "true") $fontstyle .= "I";
            if ((string)($fontElement["isUnderline"] ?? 'false') == "true") $fontstyle .= "U";
        }

        if (isset($data->reportElement["key"]) && !empty($data->reportElement["key"])) {
            $height = $fontsize * $this->adjust;
        }

        $fill = ((string)($data->reportElement["mode"] ?? '') == "Opaque") ? 1 : 0;
        $stretchoverflow = ((string)($data["isStretchWithOverflow"] ?? 'false') == "true");
        $printoverflow = ((string)($data->reportElement["isPrintWhenDetailOverflows"] ?? 'false') == "true");
        if ($printoverflow) $stretchoverflow = false;

        $textcolor = $this->report->getColor($data->reportElement["forecolor"] ?? '#000000');
        $fillcolor = $this->report->getColor($data->reportElement["backcolor"] ?? '#FFFFFF');
        $drawcolor = ["r" => 0, "g" => 0, "b" => 0]; // Default for border
        
        $box = [];
        $border = 0;
        if (isset($data->box)) {
            $border = $this->formatBox($data->box);
            $box = $data->box;
        }

        $lineHeightRatio = $this->getLineHeightRatio($data->textElement->paragraph ?? null);

        Instructions::addInstruction(["type" => "setCellHeightRatio", "ratio" => $lineHeightRatio]);
        Instructions::addInstruction(["type" => "SetXY", "x" => (int)$data->reportElement["x"], "y" => (int)$data->reportElement["y"]]);
        Instructions::addInstruction(["type" => "SetTextColor", "r" => $textcolor["r"], "g" => $textcolor["g"], "b" => $textcolor["b"]]);
        Instructions::addInstruction(["type" => "SetDrawColor", "r" => $drawcolor["r"], "g" => $drawcolor["g"], "b" => $drawcolor["b"]]);
        Instructions::addInstruction(["type" => "SetFillColor", "r" => $fillcolor["r"], "g" => $fillcolor["g"], "b" => $fillcolor["b"]]);
        Instructions::addInstruction(["type" => "SetFont", "font" => $font, "pdfFontName" => (string)($data->textElement->font["pdfFontName"] ?? ''), "fontstyle" => $fontstyle, "fontsize" => $fontsize]);

        Instructions::addInstruction([
            "type" => "MultiCell",
            "width" => (int) $data->reportElement["width"],
            "height" => $height,
            "txt" => $text,
            "border" => $border,
            "align" => $align,
            "fill" => $fill,
            "hidden_type" => "statictext",
            "printWhenExpression" => $printWhenExpression,
            "soverflow" => $stretchoverflow,
            "poverflow" => $printoverflow,
            "rotation" => $rotation,
            "valign" => $valign,
            "link" => null,
            "x" => (int) $data->reportElement["x"],
            "y" => (int) $data->reportElement["y"],
            "box" => $box,
            'writeHTML' => false
        ]);
    }

    

    private function getVerticalAlignment($align)
    {
        switch ($align) {
            case "Bottom": return "B";
            case "Middle": return "C";
            default: return "T";
        }
    }

    private function getLineHeightRatio($paragraph)
    {
        if (!$paragraph || !isset($paragraph["lineSpacing"])) {
            return 1;
        }
        switch ($paragraph["lineSpacing"]) {
            case "1_1_2": return 1.5;
            case "Double": return 2.0;
            case "Proportional": return (float) ($paragraph["lineSpacingSize"] ?? 1);
            default: return 1;
        }
    }
}

?>
