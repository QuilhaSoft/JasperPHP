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
	class PageHeader extends Element
	{
		public function generate($obj = null)
		{
			$dbData = $obj->dbData;
			$row = $dbData->fetchObject($this->class);
			$rowArray =is_array($row)?$row:get_object_vars($row);
			foreach ($this->children as $child)
			{
				// se for objeto
				if (is_object($child))
				{
					$dataAndParameters = array_merge($_POST,$rowArray);
					parent::generate(array($obj,$dataAndParameters));
					JasperPHP\Pdf::addInstruction(array ("type"=>"SetY_axis","y_axis"=>$this->children['0']->height));

				}
			}
		}
	}
?>