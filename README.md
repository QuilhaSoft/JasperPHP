# JasperPHP
Project to generate reports created with the JasperSoft Studio application<br>
Library pure php, without a java server or Jasper Server
# Suported tags/conponents
<table>
    <tr>
        <td>TAG/component</td>
        <td>Status</td>
        <td>TAG/component</td>
        <td>Status</td>
    </tr>
    <tr>
        <td colspan="4">Basic Elements</td>
    </tr>
    <tr>
        <td>Text Field</td>
        <td>OK</td>
        <td>Static Text</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Image</td>
        <td>OK</td>
        <td>Break</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Rectangle</td>
        <td>OK</td>
        <td>Line</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>SubReport*</td>
        <td>OK</td>
        <td>Barcode</td>
        <td>OK</td>
    </tr>
    <tr>
        <td colspan="4">Composite Elements</td>
    </tr>
    <tr>
        <td>Page Number</td>
        <td>OK</td>
        <td>Total Pages</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Corrent Date</td>
        <td>OK</td>
        <td>Page X of Y</td>
        <td>OK</td>
    </tr>
    <tr>
        <td colspan="4">Bands</td>
    </tr>
    <tr>
        <td>Title</td>
        <td>OK</td>
        <td>Page Header</td>
        <td></td>
    </tr>
    <tr>
        <td>Detail</td>
        <td>OK</td>
        <td>Column Header</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Column Footer</td>
        <td>OK</td>
        <td>Page Footer</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Background</td>
        <td>OK</td>
    </tr>

</table>
* Subreports are supported recursively and unlimited

# Other features
<lu>
    <li>active record</li>
</lu>
<br>

#Código de exemplo
```php
<?php
use JasperPHP\Report;
use JasperPHP\Report2XLS;
use JasperPHP\ado\TTransaction;
use JasperPHP\ado\TLoggerHTML;

//use PHPexcel as PHPexcel;
/**
* classe TJasper
* encapsula uma ação
*
* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.net>
* @version  2015.03.11
* @access   restrict
* 
* 2015.03.11 -- criação
**/
class TJasper
{
    private $report;
    private $type;

    /**
    * método __construct()
    * instancia uma nova ação
    * @param $action = método a ser executado
    */
    public function __construct($jrxml,$param)
    {
        $xmlFile=  $jrxml;
        $this->type = (array_key_exists('type',$param))?$param['type']:'pdf';
        error_reporting(0);
        switch ($this->type)
        {
            case 'pdf': 
                $this->report =new JasperPHP\Report($xmlFile,$param);
                JasperPHP\Pdf::prepare($this->report);
                break;
            case 'xls':
                JasperPHP\Excel::prepare();
                $this->report =new JasperPHP\Report2XLS($xmlFile,$param);
                
                break;
        }
    }
    public function outpage($type='pdf'){
        $this->report->generate();
        $this->report->out();
        switch ($this->type)
        {
            case 'pdf':
                $pdf  = JasperPHP\Pdf::get();
                $pdf->Output('Relatorio.pdf',"I");
                break;
            case 'xls':
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="01simple.xls"');
                header('Cache-Control: max-age=0');
                // If you're serving to IE 9, then the following may be needed
                header('Cache-Control: max-age=1');
                // If you're serving to IE over SSL, then the following may be needed
                header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header ('Pragma: public'); // HTTP/1.0
                $objWriter = PHPExcel_IOFactory::createWriter($this->report->wb, 'Excel5');
                $objWriter->save('php://output');
            break;
        }
        
    }
    public function setVariable($name,$value){
        $this->report->arrayVariable[$name]['initialValue'] = $value ;
    }
}
require('autoloader.php') ;
TTransaction::open('dev');
$jasper = new TJasper('template.jrxml',$_GET);
$jasper->outpage();
?>

```

# Como instalar e usar este exemplo
Defina as configurações do seu banco de dados em config\dev.ini<br>
URL de exemplo:<br>
http://localhost/vendor/quilhasoft/JasperPHP/Tjasper.class.php?locacoes_dia_repasse=20&eventos_mes_ref=0816<br>
Os parametros passados pela URL serao os paramatros configurados dentro do arquivo jrxml.<br>
OBS.: este projeto depende de tecnickcom/tcpdf":"6.2.*" e deve estar disponivel em autoload pelo seu composer<br>
# Usando o compser
Adicione "quilhasoft/jasperphp":"dev-master" ao seu composer e atualize seu autoload
