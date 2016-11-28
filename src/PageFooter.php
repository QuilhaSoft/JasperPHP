<?php
namespace JasperPHP;
use \JasperPHP;
/**
 * classe TLabel
 * classe para construção de rótulos de texto
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 * 
 * 2015.03.11 -- criação
**/
class PageFooter extends Element
{
	public function generate($obj = null)
	{
        $height = (string)$this->children['0']->objElement['height'];
		JasperPHP\Pdf::addInstruction(array ("type"=>"resetY_axis"));
		JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>($obj->arrayPageSetting["pageHeight"]-$obj->arrayPageSetting["topMargin"]-$this->children['0']->height-$obj->arrayPageSetting["bottomMargin"])));
		parent::generate($obj);
		JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$height));
	}
}
?>