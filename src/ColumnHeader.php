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
	class ColumnHeader extends Element
	{
		public function generate($obj = null)
		{
			if($this->children['0']->objElement->splitType=='Stretch' || $this->children['0']->objElement->splitType=='Prevent'){
				JasperPHP\Pdf::addInstruction(array ("type"=>"PreventY_axis","y_axis"=>$this->children['0']->objElement['height']));
			}
			parent::generate($obj);
            //var_dump($this->children['0']);
			JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$this->children['0']->objElement['height']));
		}
	}
?>