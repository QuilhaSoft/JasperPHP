<?php
namespace JasperPHP;
use JasperPHP;
/**
* classe Table
* classe para construção de tabela
*
* @author  Leandro Gama <gamadoleo@hotmail.com>
* @version  2021.06.30
* @access   restrict
* 
* 2021.06.30 -- criação
**/
class Table extends Element
{
	private $arrayVariable;
	static private $page=0;
	
	public function getColorFill($data){
		if (isset($data["backcolor"])) {
            return array('backcolor' => $data["backcolor"], "r" => hexdec(substr($data["backcolor"], 1, 2)), "g" => hexdec(substr($data["backcolor"], 3, 2)), "b" => hexdec(substr($data["backcolor"], 5, 2)));
        }
	}
	
	public function formatPen($box,$pen)
    {
		if(!isset($pen["lineColor"]))
			$pen["lineColor"]= $box->pen["lineColor"];//get default box
				
		//default
	    if (isset($pen["lineColor"])) {
            $drawcolor = array(
                "r" => hexdec(substr($pen["lineColor"], 1, 2)),
                "g" => hexdec(substr($pen["lineColor"], 3, 2)),
                "b" => hexdec(substr($pen["lineColor"], 5, 2))
            );
        }

        $dash = "";
        if (isset($pen["lineStyle"])) {
            if ($pen["lineStyle"] == "Dotted")
                $dash = "1,1";
            elseif ($pen["lineStyle"] == "Dashed")
                $dash = "4,2";

            // Dotted Dashed
        }

        return array(
            'width' => $pen["lineWidth"] + 0,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => $dash,
            'phase' => 0,
            'color' => $drawcolor
        );
    }
	
	public function formatBox($box){
		$border = Array();
		/*
		<topPen lineWidth="0.0" lineStyle="Solid" lineColor="#000000"/>
		<leftPen lineWidth="0.0" lineStyle="Solid" lineColor="#000000"/>
		<bottomPen lineWidth="0.0" lineStyle="Solid" lineColor="#000000"/>
		<rightPen lineWidth="0.0" lineStyle="Solid" lineColor="#000000"/>
		$border = array(
		   'T' => array('width' => 1, 'color' => array(0,255,0), 'dash' => 4, 'cap' => 'butt'),
		   'R' => array('width' => 2, 'color' => array(255,0,255), 'dash' => '1,3', 'cap' => 'round'),
		   'B' => array('width' => 3, 'color' => array(0,0,255), 'dash' => 0, 'cap' => 'square'),
		   'L' => array('width' => 4, 'color' => array(255,0,255), 'dash' => '3,1,0.5,2', 'cap' => 'butt'),
		);
		*/
  
	
		//top border cell
        if (isset($box->topPen["lineWidth"]) && $box->topPen["lineWidth"]>0.0)			
            $border['T']=$this->formatPen($box,$box->topPen);
		//leftPen border cell
        if (isset($box->leftPen["lineWidth"]) && $box->leftPen["lineWidth"]>0.0)			
            $border['L']=$this->formatPen($box,$box->leftPen);
		//bottomPen border cell		
        if (isset($box->bottomPen["lineWidth"]) && $box->bottomPen["lineWidth"]>0.0)			
            $border['B']=$this->formatPen($box,$box->bottomPen);
		//rightPen border cell
        if (isset($box->rightPen["lineWidth"]) && $box->rightPen["lineWidth"]>0.0)
            $border['R']=$this->formatPen($box,$box->rightPen);		
		return $border;		
	}

	public function prepareColumn($column,$obj){
				$objColumn = array();
				$attributes = $column->attributes();
				$borders="";
				$box=array();
				//border definition style
				if(isset($attributes['style'])){					
					$style = $obj->getStyle($attributes['style']);
					$box=$style->box;
					$att=$style->attributes();//[mode] => Opaque	 [backcolor] => #BFE1FF
					if ($att["mode"] == "Opaque") {
						$objColumn['fill']=1;						
						$objColumn['fillcolor']=$this->getColorFill($att);
					}					
				}
				//border cell definition
				if($column->children()->box){
					$box =(object)array_merge((array)$box,(array)$column->children()->box);
				}				
				$borders = $this->formatBox($box);	
				$objColumn['borders'] = $borders;//default
				$objColumn['h']=$attributes['height'];
				foreach($column->children() as $k => $v){
					$className = "JasperPHP\\" . ucfirst($k);
					//echo $className."|";
					if (class_exists($className)) {
						$objColumn['field'] = new $className($v);
					}
				}
		return $objColumn;
	}
	
	public function variable_handler($xml_variables) {
		$this->arrayVariable = array();	
		foreach ($xml_variables as $variable) {
				$varName = (string) $variable["name"];
				$this->arrayVariable[$varName] = array("calculation" => $variable["calculation"] . "",
					"target" => $variable->variableExpression,
					"class" => $variable["class"] . "",
					"resetType" => $variable["resetType"] . "",
					"resetGroup" => $variable["resetGroup"] . "",
					"initialValue" => (string) $variable->initialValueExpression . "",
					"incrementType" => $variable['incrementType']
				);
			}
			return $this->arrayVariable;		
	}	
		
    public function generate($obj = null)
    {
        $data = $this->objElement;
		//ComponentElement
		$reportElement=$obj[2];
        $rowData = is_array($obj)?$obj[1]:null; 
        $obj = is_array($obj)?$obj[0]:$obj; 
        $x=$reportElement["x"];
        $y=$reportElement["y"];
        $width=$reportElement["width"];
        $height=$reportElement["height"];
		$variables = array();
		$borders = 'LRBT';//default
	
		$dataRowTable = array();
		$table = $data;
		$datasetRun = $table->children();
		$subDataset_name = trim($datasetRun->datasetRun->attributes()['subDataset']);				
		//subDataset 
		foreach ($obj->objElement->subDataset as $dataSet){
			$name = trim($dataSet->attributes()['name']);
			//is dataSet of table?
			if($name==$subDataset_name){			
				//get variables dataSet
				$obj->arrayVariable = $this->variable_handler($dataSet->variable);
				//prepare newParameters	and send query table				
				if (is_array($rowData)) {
				$rowArray = $rowData;
				} elseif (is_object($rowData)) {
					if (method_exists($rowData, 'toArray')) {
						$rowArray = $rowData->toArray();
					} else {
						$rowArray = get_object_vars($rowData);
					}
				}
				$newParameters = ($rowArray) ? array_merge($obj->arrayParameter, $rowArray) : $obj->arrayParameter;
				//print_r($newParameters);			
				$sql = trim($dataSet->queryString);
				$sql = $obj->prepareSql($sql,$newParameters);
				$dataRowTable = $obj->getDbDataQuery($sql);						
			}	
		}
		//get all columns	
		$columns = array();
		$i =0;
		foreach ($table->column as $key => $column) {
			$objColumn = array();
			$objColumn['w'] = $column->attributes()['width'];	
			//prepare columns bands
			if(isset($column->tableHeader)){
				$objColumn['tableHeader'] = $this->prepareColumn($column->tableHeader,$obj);
			}
			if(isset($column->columnHeader)){
				$objColumn['columnHeader'] = $this->prepareColumn($column->columnHeader,$obj);
			}
			if(isset($column->detailCell)){
				$objColumn['detailCell'] = $this->prepareColumn($column->detailCell,$obj);		
			}	
			if(isset($column->columnFooter)){
				$objColumn['columnFooter'] = $this->prepareColumn($column->columnFooter,$obj);
			}				
			if(isset($column->tableFooter)){
				$objColumn['tableFooter'] = $this->prepareColumn($column->tableFooter,$obj);
			}			
			$columns[$i] = $objColumn;
			$i++;	
		}//end each column	
		JasperPHP\Instructions::addInstruction(array("type"=>"Table", 'obj'=> $obj, 'x'=>$x,'y'=> $y,"column"=>$columns, "data"=>$dataRowTable));
		
    }
	
	public static function process($arraydata){
		$jasperObj = $arraydata['obj'];
		$pdf = JasperPHP\Instructions::get();
        $dimensions = $pdf->getPageDimensions();
		$topMargin = JasperPHP\Instructions::$arrayPageSetting["topMargin"];		
        $dbData = $arraydata['data']; 
		$columns = $arraydata['column'];
		$pdf->Ln(0);
		self::SetY_axis($arraydata['y']);
		
		$showColumnHeader = true;
		//after font definition
		$fontDefault = array();
		$fontDefault["font"]=$pdf->getFontFamily(); 
		$fontDefault["fontstyle"]=$pdf->getFontStyle();
		$fontDefault["fontsize"]=$pdf->getFontSize();
		
		$totalRows = is_array($dbData) ? count($dbData) : $dbData->rowCount();
		
		
		
		//each row data	
		$rowIndex = 0;	
        foreach($dbData as $row) {
			self::$page = JasperPHP\Instructions::$currrentPage;
			$borders = 'LRBT';//default
			$rowIndex++;
			//variables dataset================================
			$jasperObj->arrayVariable['REPORT_COUNT']["ans"] = $rowIndex;
            $jasperObj->arrayVariable['REPORT_COUNT']['target'] = $rowIndex;
            $jasperObj->arrayVariable['REPORT_COUNT']['calculation'] = null;
            $jasperObj->arrayVariable['totalRows']["ans"] = $totalRows;
            $jasperObj->arrayVariable['totalRows']["target"] = $totalRows;
            $jasperObj->arrayVariable['totalRows']["calculation"] = null;
            $jasperObj->totalRows = $totalRows;
            $jasperObj->variables_calculation($jasperObj, $row);
			//endVariables
				
			$marginLeft = JasperPHP\Instructions::$arrayPageSetting["leftMargin"];			
			//get height header and detail
			$height_header = 0;
			$height_detail = 0;	
			$height_columnFooter = 0;
			$height_tableFooter = 0;
			foreach ($arraydata['column'] as $k => $column){
				$width_column = $column['w'];		

				if($column['columnFooter']['h']>$height_columnFooter){
				$height_columnFooter=$column['columnFooter']['h'];
				}
				if($column['tableFooter']['h']>$height_tableFooter){
				$height_tableFooter=$column['tableFooter']['h'];
				}					
				
				//height header default =================================
				if($column['columnHeader']['h']>$height_header){
				$height_header=$column['columnHeader']['h'];
				}					
				//get max height
				if(isset($column['columnHeader']['field'])){
					$field = $column['columnHeader']['field'];
					$text = $jasperObj->get_expression($field->objElement->text,$row);
					//change font for height row
					$font = $field->objElement->textElement->font->attributes();
					JasperPHP\Instructions::addInstruction(array("type"=>"SetFont","font"=> $font->fontName, "fontstyle"=> (isset($font->isBold)?"B":""), "fontsize"=>$font->size));
					JasperPHP\Instructions::runInstructions();					
					$height_new = $pdf->getStringHeight($width_column,$text)*1.5;
					//return default font
					//$this->SetFont($fontDefault);
					if($height_new>$height_header){
						$height_header = $height_new;
					}
					//echo $height_header;exit;					
				}//final max height header ============================
				
				//height detail default =================================
				if($column['detailCell']['h']>$height_detail){
				$height_detail=$column['detailCell']['h'];
				}					
				//get max height
				if(isset($column['detailCell']['field'])){
					$field = $column['detailCell']['field'];
					//get line spacing
					$lineHeightRatio = 1.1;
					if (isset($field->objElement->textElement->paragraph["lineSpacing"])) {
						switch ($field->objElement->textElement->paragraph["lineSpacing"]) {
							case "1_1_2":
								$lineHeightRatio = 1.5;
								break;
							case "Double":
								$lineHeightRatio = 1.5;
								break;
							case "Proportional":
								$lineHeightRatio = $field->objElement->textElement->paragraph["lineSpacingSize"];
								break;
						}
					}
					
					$text = $jasperObj->get_expression($field->objElement->textFieldExpression,$row);
					//change font for height row
					$font = $field->objElement->textElement->font->attributes();
					//$this->SetFont(array("font"=> $font->fontName, "fontstyle"=> (isset($font->isBold)?"B":""), "fontsize"=>$font->size));	
					JasperPHP\Instructions::addInstruction(array("type"=>"SetFont","font"=> $font->fontName, "fontstyle"=> (isset($font->isBold)?"B":""), "fontsize"=>$font->size));
					JasperPHP\Instructions::runInstructions();
					$height_new = $pdf->getStringHeight($width_column,$text)*$lineHeightRatio;
					//return default font
					//$this->SetFont($fontDefault);				
					if($height_new>$height_detail){
						$height_detail = $height_new;
					}					
				}//end max height header ============================
				
			}//end get height row header and detail
			
			//check new page
			JasperPHP\Instructions::addInstruction(array("type"=>"PreventY_axis",'y_axis'=>$height_detail));
			JasperPHP\Instructions::runInstructions();
			//new page?
			if(self::$page != JasperPHP\Instructions::$currrentPage){
				$showColumnHeader=true;//repeat columnHeader
				$pdf->Ln(0);
				$y = JasperPHP\Instructions::$y_axis;
			}			
			
			//posições iniciais
			$startX = $pdf->GetX();
			$startY = JasperPHP\Instructions::$y_axis; 		
			$y = $startY;
			$x = $startX;	

			//design tableHeader ===================
			if($rowIndex==1){
				foreach ($arraydata['column'] as $k=>$column){
					$width_column = $column['w'];
					$cell = $column['tableHeader'];	
					$borders = $cell['borders'];					
					if(isset($cell['field'])){
						$field=$cell['field'];		
						$field->objElement->reportElement["x"]=$x-$marginLeft;					
						//$y = $startY+$field->objElement->reportElement["y"];					
						$field->objElement->reportElement["height"]=$cell['h'];					
						//$field->objElement->reportElement["y"]=$y;	
						$field->generate(array($jasperObj,$row));
						JasperPHP\Instructions::runInstructions();
					}
					$pdf->SetX($x);									
					//border column
					if(isset($cell['fillcolor'])){
					$pdf->SetFillColor($cell['fillcolor']["r"], $cell['fillcolor']["g"], $cell['fillcolor']["b"]);
					}
					$pdf->MultiCell($width_column,$cell['h'],"",$borders,'L',isset($cell['fill']),0,$x,$y);	
					$x = $x+$width_column;
					$pdf->SetX($x);				
				}//end column
	
			//start line	
			$pdf->Ln(0);	
			$x = $startX;
			$y = $y+$cell['h'];	
			$pdf->SetX($x);
			self::SetY_axis($cell['h']);
			}//end tableHeader
			
			//design columnHeader table ===================
			if($showColumnHeader){
				foreach ($arraydata['column'] as $k=>$column){
					$width_column = $column['w'];
					$cell = $column['columnHeader'];	
					$borders = $cell['borders'];					
					if(isset($cell['field'])){
						$field=$cell['field'];		
						$field->objElement->reportElement["x"]=$x-$marginLeft;					
						//$y = $startY+$field->objElement->reportElement["y"];					
						$field->objElement->reportElement["height"]=$height_header;					
						//$field->objElement->reportElement["y"]=$y;	
						$field->generate(array($jasperObj,$row));
						JasperPHP\Instructions::runInstructions();
					}
					$pdf->SetX($x);									
					//border column 
					if(isset($cell['fillcolor'])){
					$pdf->SetFillColor($cell['fillcolor']["r"], $cell['fillcolor']["g"], $cell['fillcolor']["b"]);
					}
					$pdf->MultiCell($width_column,$height_header,"",$borders,'L',isset($cell['fill']),0,$x,$y);	
					$x = $x+$width_column;
					$pdf->SetX($x);				
				}//end column each design header
	
			//start line	
			$pdf->Ln(0);	
			$x = $startX;
			$y = $y+$height_header;	
			$pdf->SetX($x);
			self::SetY_axis($height_header);
			
			$showColumnHeader=false;
			}//final header table
		
			
			//designer detail table ===================
			foreach ($arraydata['column'] as $column){
				$width_column = $column['w'];
				$cell = $column['detailCell'];
				$borders = $cell['borders'];
				if(isset($cell['field'])){
					$field=$cell['field'];
					$field->objElement->reportElement["x"]=$x-$marginLeft;
					$field->objElement->reportElement["height"]=$height_detail;					
					//$field->objElement->reportElement["y"]=$y;
					$field->generate(array($jasperObj,$row));
					JasperPHP\Instructions::runInstructions();
				}
				$pdf->SetX($x);					
				//border column
				if(isset($cell['fillcolor'])){
					$pdf->SetFillColor($cell['fillcolor']["r"], $cell['fillcolor']["g"], $cell['fillcolor']["b"]);
				}
				$pdf->MultiCell($width_column,$height_detail,"",$borders,'L',isset($cell['fill']),0,$x,$y);	
				$x = $x+$width_column;
				$pdf->SetX($x);					
			}//end column each design detail
			
			//start line	
			$x = $startX;
			$y=$y+$height_detail;
			$pdf->SetX($x);
			self::SetY_axis($height_detail);
			$pdf->Ln(0);
        }//end data each
		
		
		//check new page
		if($height_columnFooter>0){
			//check new page
			JasperPHP\Instructions::addInstruction(array("type"=>"PreventY_axis",'y_axis'=>$height_columnFooter));
			JasperPHP\Instructions::runInstructions();
			//new page?
			if(self::$page != JasperPHP\Instructions::$currrentPage){
				self::$page = JasperPHP\Instructions::$currrentPage;
				$pdf->Ln(0);
				$y = JasperPHP\Instructions::$y_axis;	
			}
		}
		
		//columnFooter
		foreach ($arraydata['column'] as $column){
			$width_column = $column['w'];
			if(isset($column['columnFooter'])){
				$cell = $column['columnFooter'];
				$borders = $cell['borders'];
				
				//echo $height."<br/>";
				if(isset($cell['field'])){
					$field=$cell['field'];
					$field->objElement->reportElement["x"]=$x-$marginLeft;
					$field->objElement->reportElement["height"]=$height_columnFooter;					
					//$field->objElement->reportElement["y"]=$y;
					$field->generate(array($jasperObj,null));
					JasperPHP\Instructions::runInstructions();
				}
				
				$pdf->SetX($x);					
				//border column
				if(isset($cell['fillcolor'])){
					$pdf->SetFillColor($cell['fillcolor']["r"], $cell['fillcolor']["g"], $cell['fillcolor']["b"]);
				}
				$pdf->MultiCell($width_column,$height_columnFooter,"",$borders,'L',isset($cell['fill']),0,$x,$y);	
				$x = $x+$width_column;
				$pdf->SetX($x);
			}else{
				break;
			}
		}
		//new line start
		$y=$y+$height_columnFooter;
		$x = $startX;
		$pdf->SetX($x);
		self::SetY_axis($height_columnFooter);
			
		//check new page
		if($height_tableFooter>0){
			//check new page
			JasperPHP\Instructions::addInstruction(array("type"=>"PreventY_axis",'y_axis'=>$height_tableFooter));
			JasperPHP\Instructions::runInstructions();
			//new page?
			if(self::$page != JasperPHP\Instructions::$currrentPage){
				self::$page = JasperPHP\Instructions::$currrentPage;
				$pdf->Ln(0);
				$y = JasperPHP\Instructions::$y_axis;				
			}
		}	
		
		//tableFooter
		foreach ($arraydata['column'] as $column){
			$width_column = $column['w'];
			if(isset($column['tableFooter'])){
				$cell = $column['tableFooter'];
				$borders = $cell['borders'];
				
				//echo $height."<br/>";
				if(isset($cell['field'])){
					$field=$cell['field'];
					$field->objElement->reportElement["x"]=$x-$marginLeft;
					$field->objElement->reportElement["height"]=$height_tableFooter;					
					//$field->objElement->reportElement["y"]=$y;
					$field->generate(array($jasperObj,null));
					JasperPHP\Instructions::runInstructions();
				}
				
				$pdf->SetX($x);					
				//border column
				if(isset($cell['fillcolor'])){
					$pdf->SetFillColor($cell['fillcolor']["r"], $cell['fillcolor']["g"], $cell['fillcolor']["b"]);
				}
				$pdf->MultiCell($width_column,$height_tableFooter,"",$borders,'L',isset($cell['fill']),0,$x,$y);	
				$x = $x+$width_column;
				$pdf->SetX($x);			
			}else{
				break;
			}
		}
		$y=$y+$height_tableFooter;
		$x = $startX;
		$pdf->SetX($x);
		self::SetY_axis($height_tableFooter+10);
	}
	
	static function SetY_axis($addY_axis){
		JasperPHP\Instructions::addInstruction(array("type" => "SetY_axis", "y_axis" => $addY_axis));
		JasperPHP\Instructions::runInstructions();		
	}
	
}
