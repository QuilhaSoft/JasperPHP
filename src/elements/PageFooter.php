<?php

namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
 * PageFooter class
 * This class represents the page footer band in a Jasper report.
 */
class PageFooter extends Element
{
    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
    }

    public function generate()
    {
        // Use lastRowData for footer context, but fall back to an empty array if it's null.
        $rowData = $this->report->lastRowData ?? [];

        $bandHeight = (int) $this->children[0]->objElement['height'];
        $pageHeight = (int) $this->report->arrayPageSetting["pageHeight"];
        $topMargin = (int) $this->report->arrayPageSetting["topMargin"];
        $bottomMargin = (int) $this->report->arrayPageSetting["bottomMargin"];

        $yAxis = $pageHeight - $topMargin - $bandHeight - $bottomMargin;

        Instructions::addInstruction(["type" => "resetY_axis"]);
        Instructions::addInstruction(["type" => "SetY_axis", "y_axis" => $yAxis]);
        
        Instructions::$processingPageFooter = true;
        
        // Temporarily set rowData for the parent generate call
        $originalRowData = $this->report->rowData;
        $this->report->rowData = $rowData;
        parent::generate();
        $this->report->rowData = $originalRowData;

        Instructions::$processingPageFooter = false;
        
        Instructions::addInstruction(["type" => "SetY_axis", "y_axis" => $bandHeight]);
    }
}
