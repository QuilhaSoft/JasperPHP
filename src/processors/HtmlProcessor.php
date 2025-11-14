<?php

namespace JasperPHP\processors;

use JasperPHP\elements\Report;

class HtmlProcessor
{
    private static $instance;
    private $htmlBody = '';
    private $report;
    private $styles = [];
    
    // Cursor state
    private $x = 0;
    private $y = 0;
    private $y_axis = 0;

    private function __construct() {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function get()
    {
        return self::getInstance();
    }

    public static function prepare(Report $report)
    {
        $instance = self::getInstance();
        $instance->report = $report;
        $instance->y_axis = $report->arrayPageSetting["topMargin"] ?? 0;
        $instance->styles = [
            'font-family' => 'Arial',
            'font-size' => '10pt',
            'font-weight' => 'normal',
            'font-style' => 'normal',
            'color' => '#000000',
            'background-color' => 'transparent',
            'border-color' => '#000000',
            'border-width' => '0.2px'
        ];
        $instance->htmlBody = '';
    }

    public function SetY_axis($options)
    {
        $this->y_axis += (int)$options['y_axis'];
    }

    public function resetY_axis($options)
    {
        $this->y_axis = $this->report->arrayPageSetting["topMargin"] ?? 0;
    }

    public function SetXY($options)
    {
        $this->x = $options["x"] + ($this->report->arrayPageSetting["leftMargin"] ?? 0);
        $this->y = $options["y"] + $this->y_axis;
    }

    public function SetFont($options)
    {
        $this->styles['font-family'] = $options['font'] ?: 'Arial';
        $this->styles['font-size'] = ($options['fontsize'] ?: 10) . 'pt';
        $this->styles['font-weight'] = (strpos($options['fontstyle'], 'B') !== false) ? 'bold' : 'normal';
        $this->styles['font-style'] = (strpos($options['fontstyle'], 'I') !== false) ? 'italic' : 'normal';
    }

    public function SetFillColor($options) { $this->styles['background-color'] = "rgb({$options['r']}, {$options['g']}, {$options['b']})"; }
    public function SetTextColor($options) { $this->styles['color'] = "rgb({$options['r']}, {$options['g']}, {$options['b']})"; }
    public function SetDrawColor($options) { $this->styles['border-color'] = "rgb({$options['r']}, {$options['g']}, {$options['b']})"; }
    public function SetLineWidth($options) { $this->styles['border-width'] = $options['width'] . 'px'; }

    public function Line($options)
    {
        $x1 = $options['x1'] + ($this->report->arrayPageSetting["leftMargin"] ?? 0);
        $y1 = $options['y1'] + $this->y_axis;
        $x2 = $options['x2'] + ($this->report->arrayPageSetting["leftMargin"] ?? 0);
        $y2 = $options['y2'] + $this->y_axis;
        
        $style = "left:{$x1}px; top:{$y1}px; width:" . ($x2 - $x1) . "px; height:" . ($y2 - $y1) . "px; border-top: {$this->styles['border-width']} solid {$this->styles['border-color']};";
        $this->htmlBody .= "\t\t" . '<div class="element line" style="' . $style . '"></div>' . "\n";
    }

    public function Rect($options)
    {
        $x = $options['x'] + ($this->report->arrayPageSetting["leftMargin"] ?? 0);
        $y = $options['y'] + $this->y_axis;
        $w = $options['width'];
        $h = $options['height'];
        
        $css = "left:{$x}px; top:{$y}px; width:{$w}px; height:{$h}px; border: {$this->styles['border-width']} solid {$this->styles['border-color']};";
        if (isset($options['draw']) && strpos($options['draw'], 'F') !== false) {
            $css .= 'background-color: ' . $this->styles['background-color'] . ';';
        }
        $this->htmlBody .= "\t\t" . '<div class="element rect" style="' . $css . '"></div>' . "\n";
    }

    public function Image($options)
    {
        $x = $options['x'] + ($this->report->arrayPageSetting["leftMargin"] ?? 0);
        $y = $options['y'] + $this->y_axis;
        $w = $options['width'];
        $h = $options['height'];
        $file = $options['path'];
        
        $style = "left:{$x}px; top:{$y}px; width:{$w}px; height:{$h}px;";
        $this->htmlBody .= "\t\t" . '<img src="' . $file . '" class="element image" style="' . $style . '">' . "\n";
    }

    public function Cell($options)
    {
        $w = $options['width'];
        $h = $options['height'];
        $txt = $options['txt'];
        $border = $options['border'];
        $ln = $options['ln'];
        $align = $options['align'];
        $fill = $options['fill'];

        $style = "left:{$this->x}px; top:{$this->y}px; width:{$w}px; height:{$h}px; line-height:{$h}px;";
        
        foreach ($this->styles as $key => $value) {
            $style .= "{$key}:{$value};";
        }
        if ($border) { $style .= "border: {$this->styles['border-width']} solid {$this->styles['border-color']};"; }
        if ($fill) { $style .= "background-color: {$this->styles['background-color']};"; }

        $alignMap = ['L' => 'left', 'C' => 'center', 'R' => 'right'];
        $style .= "text-align:" . ($alignMap[$align] ?? 'left') . ";";

        $text = htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
        $this->htmlBody .= "\t\t" . '<div class="element cell" style="' . $style . '">' . $text . '</div>' . "\n";

        $this->x += $w;
        if ($ln == 1) {
            $this->y += $h;
            $this->x = ($this->report->arrayPageSetting["leftMargin"] ?? 0);
        }
    }
    
    public function MultiCell($options)
    {
        $options['txt'] = str_replace("\n", "<br>", $options['txt']);
        $this->Cell($options);
    }

    public function AddPage($options)
    {
        $this->htmlBody .= "\t</div>\n\t<div class=\"page\">\n";
        $this->y_axis = $this->report->arrayPageSetting["topMargin"] ?? 0;
    }

    public function AliasNbPages($alias = '{nb}') {}
    public function PageNo() { return ''; }

    public function getHtmlContent()
    {
        $pageWidth = $this->report->pageWidth ?? 595;
        $pageHeight = $this->report->pageHeight ?? 842;

        $html = "<html>\n<head>\n\t<title>JasperPHP HTML Report</title>\n";
        $html .= "\t<style>\n";
        $html .= "\t\tbody { font-family: Arial, sans-serif; }\n";
        $html .= "\t\t.page { position: relative; width: " . $pageWidth . "px; min-height: " . $pageHeight . "px; margin: auto; border: 1px solid #ccc; overflow: hidden; page-break-after: always; }\n";
        $html .= "\t\t.element { position: absolute; }\n";
        $html .= "\t\t.cell { box-sizing: border-box; white-space: pre; overflow: hidden; padding: 2px; }\n";
        $html .= "\t\t.rect { box-sizing: border-box; }\n";
        $html .= "\t\t.line { position: absolute; }\n";
        $html .= "\t\t@media print { body, .page { margin: 0; border: 0; } }\n";
        $html .= "\t</style>\n";
        $html .= "</head>\n<body>\n\t<div class=\"page\">\n" . $this->htmlBody . "\t</div>\n</body>\n</html>";
        
        $totalPages = substr_count($this->htmlBody, 'class=\"page\"') + 1;
        $html = str_replace('{:ptp:}', $totalPages, $html);
        
        return $html;
    }

    public function out()
    {
        header('Content-Type: text/html');
        echo $this->getHtmlContent();
    }
}