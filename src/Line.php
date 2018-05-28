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
	class Line extends Element
	{


		public function generate($obj = null)
		{
		   
            $rowData = is_array($obj)?$obj[1]:null;
            $data = $this->objElement;
            $obj = is_array($obj)?$obj[0]:$obj; 
			$drawcolor=array("r"=>0,"g"=>0,"b"=>0);
			$hidden_type="line";
			$linewidth  ='';
            $dash = '';
			if($data->graphicElement->pen["lineWidth"]>0)
				$linewidth=$data->graphicElement->pen["lineWidth"];

			/*
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
			*/
			if(isset($data->graphicElement->pen["lineStyle"])) {
				if($data->graphicElement->pen["lineStyle"]=="Dotted")
					$dash="0,1";
				elseif($data->graphicElement->pen["lineStyle"]=="Dashed")
					$dash="4,2"; 
			}



			if(isset($data->reportElement["forecolor"])) {
				$drawcolor=array("r"=>hexdec(substr($data->reportElement["forecolor"],1,2)),"g"=>hexdec(substr($data->reportElement["forecolor"],3,2)),"b"=>hexdec(substr($data->reportElement["forecolor"],5,2)));
			}
			//        $this->pointer[]=array("type"=>"SetDrawColor","r"=>$drawcolor["r"],"g"=>$drawcolor["g"],"b"=>$drawcolor["b"],"hidden_type"=>"drawcolor");
			if(isset($data->reportElement['positionType'])&&$data->reportElement['positionType']=="FixRelativeToBottom") {
				$hidden_type="relativebottomline";
			}

			$style=array('color'=>$drawcolor,'width'=>$linewidth,'dash'=>$dash);
			//        
            $printWhenExpression = $data->reportElement->printWhenExpression;
            preg_match_all("/P{(\w+)}/",$printWhenExpression ,$matchesP);
            preg_match_all("/F{(\w+)}/",$printWhenExpression ,$matchesF);
            preg_match_all("/V{(\w+)}/",$printWhenExpression ,$matchesV);
            if($matchesP>0){
                foreach($matchesP[1] as $macthP){
                    $printWhenExpression = str_ireplace(array('$P{'.$macthP.'}','"'),array($obj->arrayParameter[$macthP],''),$printWhenExpression); 
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

			if($data->reportElement["width"][0]+0 > $data->reportElement["height"][0]+0)    //width > height means horizontal line
			{
				JasperPHP\Pdf::addInstruction(array("type"=>"Line", "x1"=>$data->reportElement["x"]+0,"y1"=>$data->reportElement["y"]+0,
					"x2"=>$data->reportElement["x"]+$data->reportElement["width"],"y2"=>$data->reportElement["y"]+$data->reportElement["height"]-1,
					"hidden_type"=>$hidden_type,"style"=>$style,"forecolor"=>$data->reportElement["forecolor"]."",
					"printWhenExpression"=>$printWhenExpression));
			}
			elseif($data->reportElement["height"][0]+0>$data->reportElement["width"][0]+0)        //vertical line
			{
				JasperPHP\Pdf::addInstruction(array("type"=>"Line", "x1"=>$data->reportElement["x"],"y1"=>$data->reportElement["y"],
					"x2"=>$data->reportElement["x"]+$data->reportElement["width"]-1,"y2"=>$data->reportElement["y"]+$data->reportElement["height"],"hidden_type"=>$hidden_type,"style"=>$style,
					"forecolor"=>$data->reportElement["forecolor"]."","printWhenExpression"=>$data->reportElement->printWhenExpression));
			}


			JasperPHP\Pdf::addInstruction(array("type"=>"SetDrawColor","r"=>0,"g"=>0,"b"=>0,"hidden_type"=>"drawcolor"));
			JasperPHP\Pdf::addInstruction(array("type"=>"SetFillColor","r"=>255,"g"=>255,"b"=>255,"hidden_type"=>"fillcolor"));
			parent::generate($obj);
		}
	}
?>