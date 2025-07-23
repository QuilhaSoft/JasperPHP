<?php

namespace JasperPHP;

/**
 * TJasper class
 * This class serves as the main entry point for generating Jasper reports.
 */
class TJasper {

    private $report;
    private $type;

    /**
     * method __construct()
     * 
     * @param $jrxml = a jrxml file name
     * @param $param = a array with params to use into jrxml report
     */
    public function __construct($jrxml, array $param, array $data = []) {
        $GLOBALS['reports'] = array();
        $xmlFile = $jrxml;
        $this->type = (array_key_exists('type', $param)) ? $param['type'] : 'pdf';
        $this->report = new Report($xmlFile, $param); // $GLOBALS['reports'][$xmlFile];
        if (!empty($data)) {
            $this->report->setData($data);
        }
        switch ($this->type) {
            case 'pdf':
                Instructions::prepare($this->report);
                break;
            // case 'xls':
            //     Instructions::setProcessor('XlsProcessor');
            //     Instructions::prepare($this->report);
            //     break;
            // case 'xlsx':                //Process use 'PHPOffice/PhpSpreadsheet'
            //     Instructions::setProcessor('XlsxProcessor');
            //     Instructions::prepare($this->report);
            //     break;            case 'html':                Instructions::setProcessor('HtmlProcessor');                Instructions::prepare($this->report);                break;
        }
    }

    public function outpage($type = 'pdf') {
        $this->report->generate();
        $this->report->out();
        switch ($this->type) {
            case 'pdf':
                $pdf = Instructions::get();
                $pdf->Output('report.pdf', "I");
                break;
            // case 'xls':
            //     header('Content-Type: application/vnd.ms-excel');
            //     header('Content-Disposition: attachment;filename="01simple.xls"');
            //     header('Cache-Control: max-age=0');
            //     // If you're serving to IE 9, then the following may be needed
            //     header('Cache-Control: max-age=1');
            //     // If you're serving to IE over SSL, then the following may be needed
            //     header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            //     header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            //     header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            //     header('Pragma: public'); // HTTP/1.0
            //     $objWriter = PHPExcel_IOFactory::createWriter(Instructions::$objOutPut, 'Excel5');
            //     $objWriter->save('php://output');
            //     break;
            // case 'xlsx':
            //     header('Content-Type: application/vnd.ms-excel');
            //     header('Content-Disposition: attachment;filename="01simple.xls"');
            //     header('Cache-Control: max-age=0');
            //     // If you're serving to IE 9, then the following may be needed
            //     header('Cache-Control: max-age=1');
            //     // If you're serving to IE over SSL, then the following may be needed
            //     header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            //     header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            //     header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            //     header('Pragma: public'); // HTTP/1.0
            //     $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx(Instructions::$objOutPut);
            //     $objWriter->save('php://output');
            //     break;
            case 'html':
                $html = Instructions::get();
                $html->out();
                break;
        }
    }

    public function setVariable($name, $value) {
        $this->report->arrayVariable[$name]['initialValue'] = $value;
    }

}
