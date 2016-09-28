<?php
namespace JasperPHP;
use \JasperPHP;
/**
 * classe TLabel
 * classe para construзгo de rуtulos de texto
 *
 * @author   Rogerio Muniz de Castro <rogerio@singularsistemas.net>
 * @version  2015.03.11
 * @access   restrict
 * 
 * 2015.03.11 -- criaзгo
**/
class PageFooter extends Element
{
	public function generate($obj = null)
	{
		JasperPHP\Pdf::addInstruction(array ("type"=>"resetY_axis"));
		JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>($obj->arrayPageSetting["pageHeight"]-$obj->arrayPageSetting["topMargin"]-$this->children['0']->height-$obj->arrayPageSetting["bottomMargin"])));
		parent::generate($dbData);
		JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$this->children['0']->height));
	}
}
?>