<?php
namespace JasperPHP\elements;

use JasperPHP\elements\Element;
use JasperPHP\core\Instructions;

/**
* classe Table
* classe para construção de tabela
*
* @author  Leandro Gama <gamadoleo@hotmail.com>
* @version  2021.06.30
* @access   restrict
* 
* 2021.06.30 -- criação
**/
class Table extends Element
{
    private $arrayVariable;
    private $datasetRun;

    public function __construct($objElement, $report = null)
    {
        parent::__construct($objElement, $report);
        $this->datasetRun = $this->objElement->children()->datasetRun;
    }

    public function generate()
    {
        $subDatasetName = trim((string) $this->datasetRun->attributes()['subDataset']);
        $subDataset = $this->report->getSubDataset($subDatasetName);

        if (!$subDataset) {
            return; // Or throw an exception
        }

        // Handle dataset parameters
        $newParameters = $this->report->arrayParameter;
        if (isset($this->datasetRun->datasetParameter)) {
            foreach ($this->datasetRun->datasetParameter as $parameter) {
                $paramName = (string) $parameter['name'];
                $expression = (string) $parameter->datasetParameterExpression;
                $newParameters[$paramName] = $this->report->get_expression($expression, $this->report->rowData);
            }
        }
        
        // Query the database for the table's data
        $sql = trim((string) $subDataset->queryString);
        $sql = $this->report->prepareSql($sql, $newParameters);
        $data = $this->report->getDbDataQuery($sql);

        // Prepare columns structure
        $columns = [];
        foreach ($this->objElement->column as $column) {
            $columns[] = $this->prepareColumn($column);
        }

        // Add instruction to process the table
        Instructions::addInstruction([
            "type" => "Table",
            'tableElement' => $this,   // Pass the table object itself
            "columns" => $columns,
            "data" => $data,
            "x" => (int) $this->objElement->reportElement["x"],
            "y" => (int) $this->objElement->reportElement["y"]
        ]);
    }

    public function prepareColumn($column)
    {
        $objColumn = [
            'w' => (int) $column->attributes()['width'],
            'h' => (int) $column->attributes()['height']
        ];

        $bands = ['tableHeader', 'columnHeader', 'detailCell', 'columnFooter', 'tableFooter'];
        foreach ($bands as $bandName) {
            if (isset($column->$bandName)) {
                $objColumn[$bandName] = $this->prepareCell($column->$bandName);
            }
        }
        return $objColumn;
    }

    public function prepareCell($cellElement)
    {
        $cell = [
            'h' => (int) $cellElement->attributes()['height'],
            'style' => (string) $cellElement->attributes()['style'],
            'borders' => [],
            'fill' => false,
            'fillcolor' => null,
            'field' => null
        ];

        $style = $this->report->getStyle($cell['style']);
        $box = new \SimpleXMLElement('<box/>'); // Default empty box

        if ($style) {
            if (isset($style->box)) {
                $box = $style->box;
            }
            $att = $style->attributes();
            if (isset($att["mode"]) && $att["mode"] == "Opaque") {
                $cell['fill'] = true;
                $cell['fillcolor'] = $this->report->getColor($att["backcolor"]);
            }
        }

        if (isset($cellElement->children()->box)) {
            $box = (object) array_merge((array) $box, (array) $cellElement->children()->box);
        }
        $cell['borders'] = $this->formatBox($box);

        foreach ($cellElement->children() as $k => $v) {
            $className = "JasperPHP\\elements\\" . ucfirst($k);
            if (class_exists($className)) {
                // Inject the main report object into the cell's field
                $cell['field'] = new $className($v, $this->report);
            }
        }
        return $cell;
    }

    public function render($arraydata)
    {
        $pdf = Instructions::get();
        $columns = $arraydata['columns'];
        $dbData = $arraydata['data'];

        $pdf->SetX($arraydata['x']);
        $pdf->SetY($arraydata['y']);
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        // 1. Render Table Header
        $tableHeaderHeight = $this->getMaxBandHeight($columns, 'tableHeader');
        if ($tableHeaderHeight > 0) {
            $this->renderRow($pdf, $columns, 'tableHeader', null, $tableHeaderHeight, $startX, $startY);
            $startY += $tableHeaderHeight;
        }

        // 2. Loop through data for Column Header, Detail, Column Footer
        $rowIndex = 0;
        $currentPage = $pdf->PageNo();

        foreach ($dbData as $rowData) {
            $rowData = (object) $rowData;
            $this->report->rowData = $rowData; // Update row data for expressions

            // Calculate dynamic heights for detail and header for the current row
            $detailHeight = $this->getDynamicRowHeight($pdf, $columns, 'detailCell', $rowData);
            $columnHeaderHeight = $this->getDynamicRowHeight($pdf, $columns, 'columnHeader', $rowData);

            // Check for page break
            $pdf->SetY($startY);
            if ($pdf->GetY() + $detailHeight > $pdf->getPageHeight() - $pdf->getBreakMargin()) {
                $pdf->AddPage();
                $startY = $pdf->GetY();
                $currentPage = $pdf->PageNo();
            }
            
            // Render Column Header on new page or first row
            if ($pdf->PageNo() != $currentPage || $rowIndex == 0) {
                 $this->renderRow($pdf, $columns, 'columnHeader', $rowData, $columnHeaderHeight, $startX, $startY);
                 $startY += $columnHeaderHeight;
                 $currentPage = $pdf->PageNo();
            }

            // Render Detail Row
            $this->renderRow($pdf, $columns, 'detailCell', $rowData, $detailHeight, $startX, $startY);
            $startY += $detailHeight;
            $rowIndex++;
        }
        
        // 3. Render Column Footer
        $columnFooterHeight = $this->getMaxBandHeight($columns, 'columnFooter');
        if ($columnFooterHeight > 0) {
            $this->renderRow($pdf, $columns, 'columnFooter', null, $columnFooterHeight, $startX, $startY);
            $startY += $columnFooterHeight;
        }

        // 4. Render Table Footer
        $tableFooterHeight = $this->getMaxBandHeight($columns, 'tableFooter');
        if ($tableFooterHeight > 0) {
            $this->renderRow($pdf, $columns, 'tableFooter', null, $tableFooterHeight, $startX, $startY);
            $startY += $tableFooterHeight;
        }
        
        $pdf->SetY($startY);
    }

    private function renderRow($pdf, $columns, $bandName, $rowData, $height, $startX, $startY)
    {
        $currentX = $startX;
        $pdf->SetY($startY);

        foreach ($columns as $column) {
            if (!isset($column[$bandName])) continue;
            
            $cell = $column[$bandName];
            $width = $column['w'];
            
            // Set fill color
            if ($cell['fill'] && $cell['fillcolor']) {
                $c = $cell['fillcolor'];
                $pdf->SetFillColor($c['r'], $c['g'], $c['b']);
            } else {
                $pdf->SetFillColor(255, 255, 255); // Reset to white
            }

            // Draw cell background and borders
            $pdf->MultiCell($width, $height, "", $cell['borders'], 'L', $cell['fill'], 0, $currentX, $startY);

            // Render field within the cell
            if (isset($cell['field'])) {
                $field = $cell['field'];
                $field->objElement->reportElement["x"] = $currentX;
                $field->objElement->reportElement["y"] = $startY;
                $field->objElement->reportElement["width"] = $width;
                $field->objElement->reportElement["height"] = $height;
                
                // Temporarily set rowData for expression evaluation inside the field
                $originalRowData = $this->report->rowData;
                $this->report->rowData = $rowData;
                $field->generate();
                $this->report->rowData = $originalRowData;
            }
            
            $currentX += $width;
        }
        Instructions::runInstructions(); // Execute all text/drawing instructions for the row
    }

    private function getDynamicRowHeight($pdf, $columns, $bandName, $rowData)
    {
        $maxHeight = 0;
        // Find the max height defined in the JRXML for this band as a baseline
        foreach ($columns as $column) {
            if (isset($column[$bandName])) {
                 $maxHeight = max($maxHeight, $column[$bandName]['h']);
            }
        }

        // Now, calculate the actual height needed for text fields
        foreach ($columns as $column) {
            if (!isset($column[$bandName]) || !isset($column[$bandName]['field'])) continue;
            
            $field = $column[$bandName]['field'];
            $width = $column['w'];
            
            if ($field instanceof \JasperPHP\elements\TextField) {
                 // Set font for calculation
                $font = $field->objElement->textElement->font;
                $pdf->SetFont((string)$font['fontName']?:'helvetica', (string)($font['isBold']?'B':''), (int)($font['size']?:10));

                $text = $this->report->get_expression((string)$field->objElement->textFieldExpression, $rowData);
                $h = $pdf->getStringHeight($width, $text);
                $maxHeight = max($maxHeight, $h);
            }
        }
        return $maxHeight;
    }
    
    private function getMaxBandHeight($columns, $bandName)
    {
        $height = 0;
        foreach ($columns as $column) {
            if (isset($column[$bandName])) {
                $height = max($height, $column[$bandName]['h']);
            }
        }
        return $height;
    }

    

    public function formatPen($pen)
    {
        $color = $this->report->getColor($pen["lineColor"] ?? '#000000');
        $dash = "";
        if (isset($pen["lineStyle"])) {
            if ($pen["lineStyle"] == "Dotted") $dash = "1,1";
            elseif ($pen["lineStyle"] == "Dashed") $dash = "4,2";
        }
        return [
            'width' => (float) ($pen["lineWidth"] ?? 0),
            'cap' => 'butt', 'join' => 'miter', 'dash' => $dash, 'phase' => 0, 'color' => $color
        ];
    }

    public function formatBox($box)
    {
        $border = [];
        if (($box->topPen["lineWidth"] ?? 0) > 0.0) $border["T"] = $this->formatPen($box->topPen);
        if (($box->leftPen["lineWidth"] ?? 0) > 0.0) $border["L"] = $this->formatPen($box->leftPen);
        if (($box->bottomPen["lineWidth"] ?? 0) > 0.0) $border["B"] = $this->formatPen($box->bottomPen);
        if (($box->rightPen["lineWidth"] ?? 0) > 0.0) $border["R"] = $this->formatPen($box->rightPen);
        return $border;
    }
}
