<?php
namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;
use JasperPHP\elements\StaticText;

/**
 * Image class
 * This class represents an image element in a Jasper report.
 */
class Image extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        $data = $this->objElement;
        $rowData = $this->report->rowData;

        $imageExpression = (string) $data->imageExpression;
        $imagePath = $this->report->get_expression($imageExpression, $rowData);
        $imagePath = str_ireplace(['"+', '" +', '+"', '+ "', '"'], '', $imagePath);

        $imageType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        $hyperlinkReference = '';
        if (isset($data->hyperlinkReferenceExpression)) {
            $hyperlinkReference = (string) $data->hyperlinkReferenceExpression;
            $hyperlinkReference = $this->report->get_expression($hyperlinkReference, $rowData);
            $hyperlinkReference = trim(str_replace('"', '', $hyperlinkReference));
        }
        
        $printWhenExpression = '';
        if (isset($data->reportElement->printWhenExpression)) {
            $printWhenExpression = (string) $data->reportElement->printWhenExpression;
            $printWhenExpression = $this->report->get_expression($printWhenExpression, $rowData);
        }

        $arraydata = [
            "type" => "Image",
            "path" => $imagePath,
            "x" => (int) $data->reportElement["x"],
            "y" => (int) $data->reportElement["y"],
            "width" => (int) $data->reportElement["width"],
            "height" => (int) $data->reportElement["height"],
            "imgtype" => $imageType,
            "link" => $hyperlinkReference,
            "hidden_type" => "image",
            "linktarget" => (string) $data["hyperlinkTarget"],
            "border" => 0,
            "fitbox" => false,
            "printWhenExpression" => $printWhenExpression,
        ];

        if (isset($data->box)) {
            $arraydata["border"] = StaticText::formatBox($data->box);
        }

        $fitbox = '';
        $hAlign = (string) ($data['hAlign'] ?? 'Left');
        $vAlign = (string) ($data['vAlign'] ?? 'Top');

        if (((string) $data['scaleImage']) !== "FillFrame") {
            switch ($hAlign) {
                case "Center": $fitbox = "C"; break;
                case "Right": $fitbox = "R"; break;
                default: $fitbox = "L"; break;
            }
            switch ($vAlign) {
                case "Middle": $fitbox .= "M"; break;
                case "Bottom": $fitbox .= "B"; break;
                default: $fitbox .= "T"; break;
            }
            $arraydata["fitbox"] = $fitbox;
        }

        Instructions::addInstruction($arraydata);
    }
}
?>
