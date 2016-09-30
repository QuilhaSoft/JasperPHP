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
class Band extends Element
{
    
	public function generate($obj = null)
	{   
		$row = is_array($obj)?$obj[1]:array();
		$obj = is_array($obj)?$obj[0]:$obj;
		if($this->children){
			foreach ($this->children as $child)
			{
				// se for objeto
				if (is_object($child))
				{
					$child->generate(array($obj,$row)); 
				}
			}
		}
	}
}
?>