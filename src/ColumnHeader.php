<?php
namespace JasperPHP;
use \JasperPHP;
	/**
	* classe TLabel
	* classe para construзгo de rуtulos de texto
	*
	* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
	* @version  2015.03.11
	* @access   restrict
	* 
	* 2015.03.11 -- criaзгo
	**/
	class ColumnHeader extends Element
	{
		public function generate($obj = null)
		{
			if($this->children['0']->splitType=='Stretch' || $this->children['0']->splitType=='Prevent'){
				JasperPHP\Pdf::addInstruction(array ("type"=>"PreventY_axis","y_axis"=>$this->children['0']->height));
			}
			parent::generate($obj);
			JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$this->children['0']->height));
		}
	}
?>