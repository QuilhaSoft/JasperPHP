<?php
namespace JasperPHP;
use JasperPHP\Element;
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
	class Summary extends Element
	{


		public function generate($dbData = null)
		{
			if($child->splitType=='Stretch' || $child->splitType=='Prevent'){
				Jsp_pdf::addInstruction(array ("type"=>"PreventY_axis","y_axis"=>$this->children['0']->height));
			}
			parent::generate($dbData);
			if($child->splitType=='Stretch' || $child->splitType=='Prevent'){
				Jsp_pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$this->children['0']->height));
			}

		}
	}
?>