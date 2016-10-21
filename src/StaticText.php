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
	class StaticText extends Element
	{


		public function generate($obj = null)
		{
			$rowData = is_array($obj)?$obj[1]:null;
			$data = $this->objElement;
			$obj = is_array($obj)?$obj[0]:$obj; 
			$data = $this->objElement;
			$align="L";
			$fill=0;
			$border=0;
			$fontsize=10;
			$font="helvetica";
			$fontstyle="";
			$textcolor = array("r"=>0,"g"=>0,"b"=>0);
			$fillcolor = array("r"=>255,"g"=>255,"b"=>255);
			$txt="";
			$rotation="";
			$drawcolor=array("r"=>0,"g"=>0,"b"=>0);
			$height=$data->reportElement["height"];
			$stretchoverflow="false";
			$printoverflow="false";
			$writeHTML = '';
			$isPrintRepeatedValues  = '';
			$valign = '';
			//$data->hyperlinkReferenceExpression=$this->analyse_expression($data->hyperlinkReferenceExpression);
			$data->hyperlinkReferenceExpression=trim(str_replace(array(" ",'"'),"",$data->hyperlinkReferenceExpression));
			if(isset($data->reportElement["forecolor"])) {

				$textcolor = array('forecolor'=>$data->reportElement["forecolor"],"r"=>hexdec(substr($data->reportElement["forecolor"],1,2)),"g"=>hexdec(substr($data->reportElement["forecolor"],3,2)),"b"=>hexdec(substr($data->reportElement["forecolor"],5,2)));
			}
			if(isset($data->reportElement["backcolor"])) {
				$fillcolor = array('backcolor'=>$data->reportElement["backcolor"],"r"=>hexdec(substr($data->reportElement["backcolor"],1,2)),"g"=>hexdec(substr($data->reportElement["backcolor"],3,2)),"b"=>hexdec(substr($data->reportElement["backcolor"],5,2)));
			}
			if($data->reportElement["mode"]=="Opaque") {
				$fill=1;
			}
			if(isset($data["isStretchWithOverflow"])&&$data["isStretchWithOverflow"]=="true") {
				$stretchoverflow="true";
			}
			if(isset($data->reportElement["isPrintWhenDetailOverflows"])&&$data->reportElement["isPrintWhenDetailOverflows"]=="true") {
				$printoverflow="true";
				$stretchoverflow="false";
			}
			if(isset($data->box)) {
				$borderset="";
				if($data->box->topPen["lineWidth"]>0)
					$borderset.="T";
				if($data->box->leftPen["lineWidth"]>0)
					$borderset.="L";
				if($data->box->bottomPen["lineWidth"]>0)
					$borderset.="B";
				if($data->box->rightPen["lineWidth"]>0)
					$borderset.="R";
				if(isset($data->box->pen["lineColor"])) {
					$drawcolor=array("r"=>hexdec(substr($data->box->pen["lineColor"],1,2)),"g"=>hexdec(substr($data->box->pen["lineColor"],3,2)),"b"=>hexdec(substr($data->box->pen["lineColor"],5,2)));
				}
                $dash="";
				if(isset($data->box->pen["lineStyle"])) {
					if($data->box->pen["lineStyle"]=="Dotted")
						$dash="0,1";
					elseif($data->box->pen["lineStyle"]=="Dashed")
						$dash="4,2"; 
					
						
					//Dotted Dashed
				}

				$border=array($borderset => array('width' => $data->box->pen["lineWidth"],
					'cap' => 'butt', 
					'join' => 'miter', 
					'dash' =>$dash,
					'phase'=>0,
					'color' =>$drawcolor));
				//array($borderset=>array('width'=>$data->box->pen["lineWidth"],
				//'cap'=>'butt'(butt, round, square),'join'=>'miter' (miter, round,bevel),
				//'dash'=>2 ("2,1","2"),
				//  'colour'=>array(110,20,30)  ));
				//&&$data->box->pen["lineWidth"]>0
				//border can be array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))



				//elseif()

			}
			if(isset($data->textElement["textAlignment"])) {
				$align=$this->get_first_value($data->textElement["textAlignment"]);
			}
			if(isset($data->textElement["verticalAlignment"])) {
				$valign="T";
				if($data->textElement["verticalAlignment"]=="Bottom")
					$valign="B";
				elseif($data->textElement["verticalAlignment"]=="Middle")
					$valign="C";
				else
					$valign="T";

			}
			if(isset($data->textElement["rotation"])) {
				$rotation=$data->textElement["rotation"];
			}
			if(isset($data->textElement->font["fontName"])) {

				//else
				//$data->text=$data->textElement->font["pdfFontName"];//$this->recommendFont($data->text);
				$font=$this->recommendFont($data->text,$data->textElement->font["fontName"],$data->textElement->font["pdfFontName"]);

			}
			if(isset($data->textElement->font["size"])) {
				$fontsize=$data->textElement->font["size"];
			}
			if(isset($data->textElement->font["isBold"])&&$data->textElement->font["isBold"]=="true") {
				$fontstyle=$fontstyle."B";
			}
			if(isset($data->textElement->font["isItalic"])&&$data->textElement->font["isItalic"]=="true") {
				$fontstyle=$fontstyle."I";
			}
			if(isset($data->textElement->font["isUnderline"])&&$data->textElement->font["isUnderline"]=="true") {
				$fontstyle=$fontstyle."U";
			}
			if(isset($data->reportElement["key"])) {
				$height=$fontsize*$this->adjust;
			}
			JasperPHP\Pdf::addInstruction(array("type"=>"SetXY","x"=>$data->reportElement["x"]+0,"y"=>$data->reportElement["y"]+0,"hidden_type"=>"SetXY"));
			JasperPHP\Pdf::addInstruction(array("type"=>"SetTextColor",'forecolor'=>$data->reportElement["forecolor"].'',"r"=>$textcolor["r"],"g"=>$textcolor["g"],"b"=>$textcolor["b"],"hidden_type"=>"textcolor"));
			JasperPHP\Pdf::addInstruction(array("type"=>"SetDrawColor","r"=>$drawcolor["r"],"g"=>$drawcolor["g"],"b"=>$drawcolor["b"],"hidden_type"=>"drawcolor"));
			JasperPHP\Pdf::addInstruction(array("type"=>"SetFillColor",'backcolor'=>$data->reportElement["backcolor"].'',"r"=>$fillcolor["r"],"g"=>$fillcolor["g"],"b"=>$fillcolor["b"],"hidden_type"=>"fillcolor"));
			JasperPHP\Pdf::addInstruction(array("type"=>"SetFont","font"=>$font,"pdfFontName"=>$data->textElement->font["pdfFontName"],"fontstyle"=>$fontstyle,"fontsize"=>$fontsize,"hidden_type"=>"font"));
			//"height"=>$data->reportElement["height"]

			//### UTF-8 characters, a must for me.    
			$txtEnc=$data->text; 

			$printWhenExpression = $data->reportElement->printWhenExpression;
			preg_match_all("/P{(\w+)}/",$printWhenExpression ,$matchesP);
			preg_match_all("/F{(\w+)}/",$printWhenExpression ,$matchesF);
			preg_match_all("/V{(\w+)}/",$printWhenExpression ,$matchesV);
			if($matchesP>0){
				foreach($matchesP[1] as $macthP){
					$printWhenExpression = str_ireplace(array('$P{'.$macthP.'}','"'),array(utf8_encode($obj->arrayParameter[$macthP]),''),$printWhenExpression); 
				}
			}if($matchesF>0){
				foreach($matchesF[1] as $macthF){
					$printWhenExpression = $obj->getValOfField($macthF,$rowData,$printWhenExpression);
				}
			}
			if($matchesV>0){
				foreach($matchesV[1] as $macthV){
					$printWhenExpression = $obj->getValOfVariable($macthV,$printWhenExpression); 
				}

			}

			JasperPHP\Pdf::addInstruction(array("type"=>"MultiCell","width"=>$data->reportElement["width"],"height"=>$height,
				"txt"=>$txtEnc,"border"=>$border,"align"=>$align,"fill"=>$fill,"hidden_type"=>"statictext",
				"printWhenExpression"=>$printWhenExpression."",
				"soverflow"=>$stretchoverflow,"poverflow"=>$printoverflow,"rotation"=>$rotation,"valign"=>$valign,"link"=>null,
				"x"=>$data->reportElement["x"]+0,"y"=>$data->reportElement["y"]+0,
                'writeHTML'=>$writeHTML));
			//### End of modification, below is the original line        
			//        $pointer=array("type"=>"MultiCell","width"=>$data->reportElement["width"],"height"=>$height,"txt"=>$data->text,"border"=>$border,"align"=>$align,"fill"=>$fill,"hidden_type"=>"statictext","soverflow"=>$stretchoverflow,"poverflow"=>$printoverflow,"rotation"=>$rotation);

			//$this->checkoverflow($pointer);
		}
	}
?>