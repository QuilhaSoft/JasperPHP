<?php
namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\elements\Table;
use JasperPHP\core\Instructions;

/**
 * ComponentElement class
 * This class represents a component element in a Jasper report, such as tables or barcodes.
 */
class ComponentElement extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        $data = $this->objElement;
        $rowData = $this->report->rowData;

        $x = $data->reportElement["x"];
        $y = $data->reportElement["y"];
        $width = $data->reportElement["width"];
        $height = $data->reportElement["height"];

        // Table
        $jrs = $data->children('jr', true);
        if (isset($jrs->table)) {
            // Pass the parent report's context to the new Table element
            $table = new Table($jrs->table, $this->report);
            $table->generate();
        }

        // Barcode
        foreach ($data->children('jr', true) as $barcodetype => $content) {
            if ($barcodetype == 'table') continue;

            $text = $this->report->get_expression($content->codeExpression, $rowData, false, $this);

            $barcodemethod = "";
            $textposition = "";
            $modulewidth = 1;

            if ($barcodetype == "barbecue") {
                $attributes = $content->attributes('', true);
                $barcodemethod = (string) $attributes->type;
                $checksum = (string) $attributes->checksumRequired;
                if ((string) $attributes->drawText == 'true') {
                    $textposition = "bottom";
                }
                $modulewidth = (string) $attributes->moduleWidth ?: 1;
            } else {
                $attributes = $content->attributes('', true);
                $barcodemethod = $barcodetype;
                $textposition = (string) $attributes->textPosition;
                $modulewidth = (string) $attributes->moduleWidth ?: 1;
            }

            Instructions::addInstruction([
                "type" => "Barcode",
                "barcodetype" => $barcodemethod,
                "x" => $x,
                "y" => $y,
                "width" => $width,
                "height" => $height,
                'textposition' => $textposition,
                'code' => $text,
                'modulewidth' => $modulewidth
            ]);
        }
    }
}
?>
