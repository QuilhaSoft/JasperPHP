<?php
namespace JasperPHP;
use JasperPHP;
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
            $height = (string)$this->children['0']->objElement['height'];
			if($this->children['0']->splitType=='Stretch' || $this->children['0']->splitType=='Prevent'){
				JasperPHP\Pdf::addInstruction(array ("type"=>"PreventY_axis","y_axis"=>$height));
			}
			parent::generate($dbData);
			if($this->children['0']->splitType=='Stretch' || $this->children['0']->splitType=='Prevent'){
				JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$height));
			}

		}
	}
?>