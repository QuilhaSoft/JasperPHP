<?php
namespace JasperPHP\elements;

/**
 * Element class
 * This class serves as the base class for all report elements.
 */
class Element
{
		public $properties;    // propriedades da TAG 
		public $name;
		public $height;
		public $splitType;
		public $radius;
		public $scaleImage;
		public $hAlign;
		public $onErrorType;
		public $pattern;
		public $y_axis;
		public $objElement;
		public $children;
        protected $report; // Adicionado para armazenar a instância do Report

        public function __construct($ObjElement, $report = null)
        {
            if(isset($ObjElement)) {
                $this->name = get_class($this);
                $this->objElement =  $ObjElement;
                $this->report = $report; // Armazena a instância do Report
                // atribui o conteúdo do label
                $attributes = $ObjElement->attributes();
                foreach($attributes as $att => $value){
                    $this->$att = $value; 
                }
                foreach($ObjElement as $obj=>$value){
                                
                    $obj = ($obj=='break')?'Breaker':$obj;

                    $className = "JasperPHP\\elements\\" . ucfirst($obj);
                    if(class_exists($className)){
                        // Passa a instância do Report para o construtor do filho
                        $this->add(new $className($value, $this->report));
                    }

                }

            }
        }


		


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
				if(get_class($Child)=="JasperPHP" . $childClassName)return $Child;
			}
		}
		public function recommendFont($utfstring,$defaultfont,$pdffont=""){

			

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
		public function generate()
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
						$child->generate();
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
