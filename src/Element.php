<?php
namespace JasperPHP;
	/**
	* classe TElement
	* classe para abstração de tags HTML
	*
	* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
	* @version  2015.03.11
	* @access   restrict
	* 
	* 2015.03.11 -- criação
	**/
	use JasperPHP;
	class Element
	{
		private $properties;    // propriedades da TAG 
		private $name;
		private $height;
		private $splitType;
		private $radius;
		private $scaleImage;
		
		public $objElement;
		public $children;

		public function __construct($ObjElement)
		{
			if(isset($ObjElement)) {
				$this->name = get_class($this);
				$this->objElement =  $ObjElement;
				// atribui o conteúdo do label
				$attributes = $ObjElement->attributes();
				foreach($attributes as $att => $value){
					$this->$att = $value; 
				}
				foreach($ObjElement as $obj=>$value){
                                
                    $obj = ($obj=='break')?'Breaker':$obj;

					$className = "JasperPHP\\".ucfirst($obj);
					if(class_exists($className)){
						$this->add(new $className($value));
					}

				}

			}
		}

		/**
		* método __set()
		* intercepta as atribuições à propriedades do objeto
		* @param $name      = nome da propriedade
		* @param $value     = valor
		*//* 
		public function __set($name, $value)
		{
		// armazena os valores atribuídos
		// ao array properties
		$this->properties[$name] = $value;
		}
		/**
		* método __get()
		* intercepta as atribuições à propriedades do objeto
		* @param $name      = nome da propriedade
		* @param $value     = valor
		*//* 
		public function __get($name)
		{
		// armazena os valores atribuídos
		// ao array properties
		if(array_key_exists($name,$this->properties)){
		return $this->properties[$name];
		}else{
		return NULL;   
		}

		}*/


		/**
		* método add()
		* adiciona um elemento filho
		* @param $child = objeto filho
		*/
		public function add($child)
		{
			$this->children[] = $child;
		}

		public function get_first_value($value) {
			return (substr($value,0,1));
		}

		public function getChildByClassName($childClassName){
			foreach($this->children as $Child){
				if(get_class($Child)=='JasperPHP\\'.$childClassName)return $Child;
			}
		}
		public function recommendFont($utfstring,$defaultfont,$pdffont=""){

			/*\p{Common}
			\p{Arabic}
			\p{Armenian}
			\p{Bengali}
			\p{Bopomofo}
			\p{Braille}
			\p{Buhid}
			\p{CanadianAboriginal}
			\p{Cherokee}
			\p{Cyrillic}
			\p{Devanagari}
			\p{Ethiopic}
			\p{Georgian}
			\p{Greek}
			\p{Gujarati}
			\p{Gurmukhi}
			\p{Han}
			\p{Hangul}
			\p{Hanunoo}
			\p{Hebrew}
			\p{Hiragana}
			\p{Inherited}
			\p{Kannada}
			\p{Katakana}
			\p{Khmer}
			\p{Lao}
			\p{Latin}
			\p{Limbu}
			\p{Malayalam}
			\p{Mongolian}
			\p{Myanmar}
			\p{Ogham}
			\p{Oriya}
			\p{Runic}
			\p{Sinhala}
			\p{Syriac}
			\p{Tagalog}
			\p{Tagbanwa}
			\p{TaiLe}
			\p{Tamil}
			\p{Telugu}
			\p{Thaana}
			\p{Thai}
			\p{Tibetan}
			\p{Yi}*/

			if($pdffont!="")
				return $pdffont;
			if(preg_match("/\p{Han}+/u", $utfstring))
				$font="cid0cs";
			elseif(preg_match("/\p{Katakana}+/u", $utfstring) || preg_match("/\p{Hiragana}+/u", $utfstring))
				$font="cid0jp";
			elseif(preg_match("/\p{Hangul}+/u", $utfstring))
				$font="cid0kr";
			else
				$font=$defaultfont;
			//echo "$utfstring $font".mb_detect_encoding($utfstring)."<br/>";

			return $font;//mb_detect_encoding($utfstring);
		}
   

		/**
		* método generate()
		* exibe a tag na tela, juntamente com seu conteúdo
		*/
		public function generate($obj = null)
		{
			// se possui conteúdo
			if ($this->children)
			{
				// percorre todos objetos filhos
				foreach ($this->children as $child)
				{
					// se for objeto
					if (is_object($child))
					{
						$child->generate($obj);
						//JasperPHP\Instructions::runInstructions();
        				//JasperPHP\Instructions::clearInstructrions();
					}
				}
				// fecha a tag
				//$this->close();
			}

		}

	}
?>
