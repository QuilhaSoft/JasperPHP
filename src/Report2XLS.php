<?php
namespace JasperPHP;
use \JasperPHP; 
use TTransaction;
use \PHPExcel;
use PHPExcel_Cell_DataType;
use PHPExcel_Style_Alignment;
use PHPExcel_Cell;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
/**
* classe TLabel
* classe para construção de rótulos de texto
*
* @author   Rogerio Muniz de Castro <rogerio@singularsistemas.net>
* @version  2015.03.11
* @access   restrict
* 
* 2015.03.11 -- criação
**/
class Report2XLS extends Report
{
	public $wb;
	public $ws;
	public $cols=array();
	public $rows=array();
	private $rowWidthOfSet = 50;
	private $rowHeightOfSet = 16;


	public function __construct($xmlFile = null,$param)
	{
		parent::__construct($xmlFile,$param);

	}
	public function runInstructions($instructions){
		//$pdf = JasperPHP\Pdf::get(); 
		//$maxheight = null; 
		$this->wb  = JasperPHP\Excel::get();
		$this->ws=$this->wb->getActiveSheet(0);
		$this->relativex = 0;
		$this->relativey = 0;
		$rowpos = 1;
		foreach($instructions as $arraydata){

			//$this->Rotate($arraydata["rotation"]);
			switch($arraydata['type']){
				case "MultiCell":       
					if($this->relativey=="")
						$this->relativey=0;
					//$this->mergeCells(    $this->relativex,  ($this->relativey+$rowpos),   ($this->cols['c'.($this->mergex+$arraydata['width'])]-1),   ($this->relativey+$rowpos)  );
					$x = intval( $arraydata['x']/$this->rowWidthOfSet );
					//if($x==0)$x=1 ;
					$y = intval( ($arraydata['y']+($arraydata['height']/2))/$this->rowHeightOfSet );
					$txt = $arraydata['txt'];
					//if($arraydata['pattern']!='')
					//   $txt= $this->formatText ($txt, $arraydata['pattern']);
					if($y>1)$rowpos++;
					if($this->debughtml)
						echo  $txt.",align:".$arraydata['align']."<br/>";

					$this->setText($x,$y+$rowpos,  $txt,$arraydata['align'], $arraydata['pattern']);                
					break;
					/*case "Cell":


					$this->SetText($this->relativex, ($this->relativey+$rowpos),$this->analyse_expression($arraydata['txt']),$arraydata['align'], $arraydata['pattern']);
					if($this->debughtml)
					echo  $txt."<br/>";

					break;   */
				case "SetY_axis":
					$y = intval( $arraydata['y_axis']/$this->rowHeightOfSet );
					$rowpos ++ ;/*
					$myx=intval($arraydata['x']);
					$myy=intval($arraydata['y']);
					$this->relativex=$this->cols['c'.$myx];
					$this->relativey=$this->rows['r'.$myy];
					$this->mergex=$myx;
					$this->mergey=$myy;//$arraydata['y'];*/
					break;
				case "PreventY_axis":
					//$rowpos++;/*
					$myx=intval($arraydata['x']);
					$myy=intval($arraydata['y']);
					$this->relativex=$this->cols['c'.$myx];
					$this->relativey=$this->rows['r'.$myy];
					$this->mergex=$myx;
					$this->mergey=$myy;//$arraydata['y'];*/
					break;
				case "SetXY":
					//$y = intval( ($arraydata['y'])/$this->rowHeightOfSet );
					//if($y>$rowpos)$rowpos = intval( ($arraydata['y'])/$this->rowHeightOfSet );
					/*$myx=intval($arraydata['x']);
					$myy=intval($arraydata['y']);
					$this->relativex=$this->cols['c'.$myx];
					$this->relativey=$this->rows['r'.$myy];
					$this->mergex=$myx;
					$this->mergey=$myy;//$arraydata['y'];*/
					break;

				case "SetFont":
					if($this->debughtml)
						echo  $arraydata['font'].",".$arraydata["fontsize"].",".$arraydata['fontstyle']."<br/>";
					$this->SetFonts($this->relativex, ($this->relativey+$rowpos),$arraydata['font'],$arraydata["fontsize"],
						$arraydata['fontstyle']);     
					//if($this->debughtml)


					break;

				case "SetTextColor":
					$cl= str_replace('#','',$arraydata['forecolor']);

					if($cl!=''){
						$this->SetTextColor($this->relativex, ($this->relativey+$rowpos),$cl);

					}
					if($this->debughtml)
						echo "$cl<br/>";
					break; 
				case "SetFillColor":
					if($arraydata['fill']==true){
						$cl= str_replace('#','',$arraydata['backcolor']);
						if($cl!=''){
							$this->SetFillColor($this->relativex, ($this->relativey+$rowpos),$cl);
						}
					}
					if($this->debughtml)
						echo "$cl<br/>";

					break;

					/*case "Line":

					if($this->uselib==false){
					$printline=false;
					if($arraydata['printWhenExpression']=="")
					$printline=true;
					//else
					//$printline=$this->analyse_expression($arraydata['printWhenExpression']);                
					if($printline){                
					$x1=$arraydata["x1"];
					$x2=$arraydata["x2"];
					$y1=$arraydata["y1"];
					$y2=$arraydata["y2"];
					$linewidth=$arraydata["style"]["width"];
					$linedash=$arraydata["style"]["dash"];
					$linecolor=  str_replace('#','',$arraydata["forecolor"]);
					$col1=$this->cols['c'.$x1];
					$col2=$this->cols['c'.$x2];
					$row1=$this->rows['r'.$y1]+$this->maxrow;
					$row2=$this->rows['r'.$y2]+$this->maxrow;
					$col1=PHPExcel_Cell::stringFromColumnIndex($col1);
					$col2=PHPExcel_Cell::stringFromColumnIndex($col2);
					if($linewidth==0)
					$linewidth=PHPExcel_Style_Border::BORDER_NONE;
					elseif($linewidth<=0.25)
					$linewidth=PHPExcel_Style_Border::BORDER_HAIR;
					elseif($linewidth<=0.5)
					$linewidth=PHPExcel_Style_Border::BORDER_THIN;
					elseif($linewidth<=0.75)
					$linewidth=PHPExcel_Style_Border::medium;
					elseif($linewidth<=1)
					$linewidth=PHPExcel_Style_Border::thick;
					else
					$linewidth=PHPExcel_Style_Border::BORDER_HAIR;
					$linewidth=PHPExcel_Style_Border::BORDER_THIN;
					if($x1==$x2){
					$styleArray = array('borders' => array('left' => array('style' =>$linewidth,'color'=>array('rgb'=>$linecolor))));
					}elseif($y1==$y2){
					$styleArray = array('borders' => array('top' => array('style' => $linewidth,'color'=>array('rgb'=>$linecolor))));
					}                  
					$this->ws->getStyle("$col1$row1:$col2$row2")->applyFromArray($styleArray);   
					}
					}else{
					//ech "12312312<br/>";
					//            echo "format 1,1:".print_r($this->ws->getFormat(0,0),true)."end line";


					}
					break;
					case "SetLineWidth":
					break; */
			}
			/*







			if($arraydata["type"]=="PreventY_axis"){
			$preventY_axis = $this->y_axis+$arraydata['y_axis'];
			$pageheight =  $this->arrayPageSetting["pageHeight"];
			$pageFooter = $this->getChildByClassName('PageFooter');
			$pageFooterHeigth =($pageFooter)?$pageFooter->children[0]->height:0;
			$topMargin = $this->arrayPageSetting["topMargin"];
			$bottomMargin = $this->arrayPageSetting["bottomMargin"] ;
			$discount = $pageheight-$pageFooterHeigth-$topMargin-$bottomMargin; //dicount heights of page parts;
			if($preventY_axis>=$discount){
			if($pageFooter)$pageFooter->generate($this);
			JasperPHP\Pdf::addInstruction(array("type"=>"resetY_axis"));
			$this->currrentPage++;
			JasperPHP\Pdf::addInstruction(array("type"=>"AddPage"));
			JasperPHP\Pdf::addInstruction(array("type"=>"setPage","value"=>$this->currrentPage,'resetMargins'=>false));
			$pageHeader = $this->getChildByClassName('PageHeader');
			if($pageHeader)$pageHeader->generate($this);
			$columnHeader = $this->getChildByClassName('ColumnHeader');
			if($columnHeader)$columnHeader->generate($this);
			$newIntrusctions = JasperPHP\Pdf::getInstructions();
			$this->runInstructions($newIntrusctions);
			}
			}
			if($arraydata["type"]=="resetY_axis"){
			$this->y_axis = $this->arrayPageSetting["topMargin"];
			}
			if($arraydata["type"]=="SetY_axis"){
			if(($this->y_axis+$arraydata['y_axis'])<=$this->arrayPageSetting["pageHeight"]){
			$this->y_axis = $this->y_axis+$arraydata['y_axis'];
			}
			}
			if($arraydata["type"]=="AddPage"){
			$pdf->AddPage();
			}
			if($arraydata["type"]=="setPage"){
			$pdf->setPage($arraydata["value"],$arraydata["resetMargins"]);
			}
			if($arraydata["rotation"]!=""){
			if($arraydata["rotation"]=="Left"){
			$w=$arraydata["width"];
			$arraydata["width"]=$arraydata["height"];
			$arraydata["height"]=$w;
			$pdf->SetXY($pdf->GetX()-$arraydata["width"],$pdf->GetY());
			}
			elseif($arraydata["rotation"]=="Right"){
			$w=$arraydata["width"];
			$arraydata["width"]=$arraydata["height"];
			$arraydata["height"]=$w;
			$pdf->SetXY($pdf->GetX(),$pdf->GetY()-$arraydata["height"]);
			}
			elseif($arraydata["rotation"]=="UpsideDown"){
			//soverflow"=>$stretchoverflow,"poverflow"
			$arraydata["soverflow"]=true;
			$arraydata["poverflow"]=true;
			//   $w=$arraydata["width"];
			// $arraydata["width"]=$arraydata["height"];
			//$arraydata["height"]=$w;
			$pdf->SetXY($pdf->GetX()- $arraydata["width"],$pdf->GetY()-$arraydata["height"]);
			}
			}
			if($arraydata["type"]=="SetFont") {
			$arraydata["font"]=  strtolower($arraydata["font"]);

			$fontfile=$this->fontdir.'/'.$arraydata["font"].'.php';
			if(file_exists($fontfile) || $this->bypassnofont==false){

			$fontfile=$this->fontdir.'/'.$arraydata["font"].'.php';

			$pdf->SetFont($arraydata["font"],$arraydata["fontstyle"],$arraydata["fontsize"],$fontfile);
			}
			else{
			$arraydata["font"]="freeserif";
			if($arraydata["fontstyle"]=="")
			$pdf->SetFont('freeserif',$arraydata["fontstyle"],$arraydata["fontsize"],$this->fontdir.'/freeserif.php');
			elseif($arraydata["fontstyle"]=="B")
			$pdf->SetFont('freeserifb',$arraydata["fontstyle"],$arraydata["fontsize"],$this->fontdir.'/freeserifb.php');
			elseif($arraydata["fontstyle"]=="I")
			$pdf->SetFont('freeserifi',$arraydata["fontstyle"],$arraydata["fontsize"],$this->fontdir.'/freeserifi.php');
			elseif($arraydata["fontstyle"]=="BI")
			$pdf->SetFont('freeserifbi',$arraydata["fontstyle"],$arraydata["fontsize"],$this->fontdir.'/freeserifbi.php');
			elseif($arraydata["fontstyle"]=="BIU")
			$pdf->SetFont('freeserifbi',"BIU",$arraydata["fontsize"],$this->fontdir.'/freeserifbi.php');
			elseif($arraydata["fontstyle"]=="U")
			$pdf->SetFont('freeserif',"U",$arraydata["fontsize"],$this->fontdir.'/freeserif.php');
			elseif($arraydata["fontstyle"]=="BU")
			$pdf->SetFont('freeserifb',"U",$arraydata["fontsize"],$this->fontdir.'/freeserifb.php');
			elseif($arraydata["fontstyle"]=="IU")
			$pdf->SetFont('freeserifi',"IU",$arraydata["fontsize"],$this->fontdir.'/freeserifbi.php');


			}

			}
			elseif($arraydata["type"]=="subreport") {    


			return $this->runSubReport($arraydata,$this->y_axis);

			}
			elseif($arraydata["type"]=="MultiCell") {

			//if($fielddata==true) {
			$this->checkoverflow($arraydata,$arraydata["txt"],$maxheight);
			//}
			}
			elseif($arraydata["type"]=="SetXY") {
			$pdf->SetXY($arraydata["x"]+$this->arrayPageSetting["leftMargin"],$arraydata["y"]+$this->y_axis);
			}
			elseif($arraydata["type"]=="Cell") {
			//                print_r($arraydata);
			//              echo "<br/>";

			$pdf->Cell($arraydata["width"],$arraydata["height"],$this->updatePageNo($arraydata["txt"]),$arraydata["border"],$arraydata["ln"],
			$arraydata["align"],$arraydata["fill"],$arraydata["link"]."",0,true,"T",$arraydata["valign"]);


			}
			elseif($arraydata["type"]=="Rect"){
			if($arraydata['mode']=='Transparent')
			$style='';
			else
			$style='FD';
			//      $pdf->SetLineStyle($arraydata['border']);
			$pdf->Rect($arraydata["x"]+$this->arrayPageSetting["leftMargin"],$arraydata["y"]+$this->y_axis,$arraydata["width"],$arraydata["height"],
			$style,$arraydata['border'],$arraydata['fillcolor']);
			}
			elseif($arraydata["type"]=="RoundedRect"){
			if($arraydata['mode']=='Transparent')
			$style='';
			else
			$style='FD';
			//
			//        $pdf->SetLineStyle($arraydata['border']);
			$pdf->RoundedRect($arraydata["x"]+$this->arrayPageSetting["leftMargin"], $arraydata["y"]+$this->y_axis, $arraydata["width"],$arraydata["height"], $arraydata["radius"], '1111', 
			$style,$arraydata['border'],$arraydata['fillcolor']);
			}
			elseif($arraydata["type"]=="Ellipse"){
			//$pdf->SetLineStyle($arraydata['border']);
			$pdf->Ellipse($arraydata["x"]+$arraydata["width"]/2+$this->arrayPageSetting["leftMargin"], $arraydata["y"]+$this->y_axis+$arraydata["height"]/2, $arraydata["width"]/2,$arraydata["height"]/2,
			0,0,360,'FD',$arraydata['border'],$arraydata['fillcolor']);
			}
			elseif($arraydata["type"]=="Image") {
			//echo $arraydata["path"];
			$path=$this->analyse_expression($arraydata["path"]);
			$imgtype=substr($path,-3);
			$arraydata["link"]=$arraydata["link"]."";
			if($imgtype=='jpg' || right($path,3)=='jpg' || right($path,4)=='jpeg')
			$imgtype="JPEG";
			elseif($imgtype=='png'|| $imgtype=='PNG')
			$imgtype="PNG";
			//echo $path;
			if(file_exists($path) || $this->left($path,4)=='http' ){  
			//$path="/Applications/XAMPP/xamppfiles/simbiz/modules/simantz/images/modulepic.jpg";
			//  $path="/simbiz/images/pendingno.png";

			if($arraydata["link"]=="") 
			$pdf->Image($path,$arraydata["x"]+$this->arrayPageSetting["leftMargin"],$arraydata["y"]+$this->y_axis,
			$arraydata["width"],$arraydata["height"],$imgtype,$arraydata["link"]);            
			else{
			//                 if($arraydata['linktarget']=='Blank' && strpos($_SERVER['HTTP_USER_AGENT'],"Safari")!==false &&     strpos($_SERVER['HTTP_USER_AGENT'],"Chrome")==false){
			//                        $href="javascript:window.open('".$arraydata["link"]."');";
			//                        $imagehtml='<A  href="'.$href.'"><img src="'.$path.'" '.
			//                                'width="'. $arraydata["width"] .'" height="'.$arraydata["height"].'" ></A>';   
			//                        $pdf->writeHTMLCell($arraydata["width"],$arraydata["height"],
			//                            $arraydata["x"]+$this->arrayPageSetting["leftMargin"],$arraydata["y"]+$this->y_axis,$imagehtml);
			//                 }
			//                else
			$pdf->Image($path,$arraydata["x"]+$this->arrayPageSetting["leftMargin"],$arraydata["y"]+$this->y_axis,
			$arraydata["width"],$arraydata["height"],$imgtype,$arraydata["link"]);




			}


			}
			elseif($this->left($path,22)==  "data:image/jpeg;base64"){
			$imgtype="JPEG";
			$img=  str_replace('data:image/jpeg;base64,', '', $path);
			$imgdata = base64_decode($img);
			$pdf->Image('@'.$imgdata,$arraydata["x"]+$this->arrayPageSetting["leftMargin"],$arraydata["y"]+$this->y_axis,$arraydata["width"],
			$arraydata["height"],'',$arraydata["link"]); 

			}
			elseif($this->left($path,22)==  "data:image/png;base64,"){
			$imgtype="PNG";
			// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			$img= str_replace('data:image/png;base64,', '', $path);
			$imgdata = base64_decode($img);


			$pdf->Image('@'.$imgdata,$arraydata["x"]+$this->arrayPageSetting["leftMargin"],$arraydata["y"]+$this->y_axis,
			$arraydata["width"],$arraydata["height"],'',$arraydata["link"]); 


			} 

			}  

			elseif($arraydata["type"]=="SetTextColor") {
			$this->textcolor_r=$arraydata['r'];
			$this->textcolor_g=$arraydata['g'];
			$this->textcolor_b=$arraydata['b'];

			if($this->hideheader==true && $this->currentband=='pageHeader')
			$pdf->SetTextColor(100,33,30);
			else
			$pdf->SetTextColor($arraydata["r"],$arraydata["g"],$arraydata["b"]);
			}
			elseif($arraydata["type"]=="SetDrawColor") {
			$this->drawcolor_r=$arraydata['r'];
			$this->drawcolor_g=$arraydata['g'];
			$this->drawcolor_b=$arraydata['b'];
			$pdf->SetDrawColor($arraydata["r"],$arraydata["g"],$arraydata["b"]);
			}
			elseif($arraydata["type"]=="SetLineWidth") {
			$pdf->SetLineWidth($arraydata["width"]);
			}
			elseif($arraydata["type"]=="break"){
			$this->print_expression($arraydata);
			if($this->print_expression_result==true) {
			if($pageFooter)$pageFooter->generate($this);
			JasperPHP\Pdf::addInstruction(array("type"=>"resetY_axis"));
			$this->currrentPage++;
			JasperPHP\Pdf::addInstruction(array("type"=>"AddPage"));
			JasperPHP\Pdf::addInstruction(array("type"=>"setPage","value"=>$this->currrentPage,'resetMargins'=>false));
			$pageHeader = $this->getChildByClassName('PageHeader');
			if($pageHeader)$pageHeader->generate($this);
			$columnHeader = $this->getChildByClassName('ColumnHeader');
			if($columnHeader)$columnHeader->generate($this);
			$newIntrusctions = JasperPHP\Pdf::getInstructions();
			$this->runInstructions($newIntrusctions);
			}
			}
			elseif($arraydata["type"]=="Line") {
			$this->print_expression($arraydata);
			if($this->print_expression_result==true) {
			$pdf->Line($arraydata["x1"]+$this->arrayPageSetting["leftMargin"],$arraydata["y1"]+$this->y_axis,
			$arraydata["x2"]+$this->arrayPageSetting["leftMargin"],$arraydata["y2"]+$this->y_axis,$arraydata["style"]);
			}
			}
			elseif($arraydata["type"]=="SetFillColor") {
			$this->fillcolor_r=$arraydata['r'];
			$this->fillcolor_g=$arraydata['g'];
			$this->fillcolor_b=$arraydata['b'];
			$pdf->SetFillColor($arraydata["r"],$arraydata["g"],$arraydata["b"]);
			}
			elseif($arraydata["type"]=="lineChart") {

			$this->generateLineChart($arraydata, $this->y_axis);
			}
			elseif($arraydata["type"]=="barChart") {

			$this->generateBarChart($arraydata, $this->y_axis,'barChart');
			}
			elseif($arraydata["type"]=="pieChart") {

			$this->generatePieChart($arraydata, $this->y_axis);
			}
			elseif($arraydata["type"]=="stackedBarChart") {

			$this->generateBarChart($arraydata, $this->y_axis,'stackedBarChart');
			}
			elseif($arraydata["type"]=="stackedAreaChart") {

			$this->generateAreaChart($arraydata, $this->y_axis,$arraydata["type"]);
			}
			elseif($arraydata["type"]=="Barcode"){

			$this->generateBarcode($arraydata, $this->y_axis);
			}
			elseif($arraydata["type"]=="CrossTab"){

			$this->generateCrossTab($arraydata, $this->y_axis);
			} */
		} 
		//$this->deleteEmptyRow($rowpos);    
	}  

	public function setText($x,$y,$txt,$align,$pattern){
		-		$myformat='';
		//if($this->uselib==0){

		//$stlen=strlen($txt);





		if(strpos($pattern,".")!==false || strpos($pattern,"#")!==false){    
			$this->ws->getCellByColumnAndRow($x, $y)->setValueExplicit($txt, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$this->ws->getStyleByColumnAndRow($x, $y)->getNumberFormat()->setFormatCode($pattern);    
		}else{
			$this->ws->getCellByColumnAndRow($x, $y)->setValueExplicit($txt, PHPExcel_Cell_DataType::TYPE_STRING);

		}
		/*if(strpos($pattern,".")!==false || strpos($pattern,"#")!==false){    

		}
		else
		$this->ws->getStyleByColumnAndRow($x, $y)->getNumberFormat()->setFormatCode('@');
		*/
		//$newstrken=($this->ws->getCellByColumnAndRow($x, $y)->getValue());
		//if($this->left($txt,1)=='0' && $stlen>$newstrken){

		// for($kkk=0;$kkk<$stlen;$kkk++){
		//$myformat.="0";
		// echo $myformat.",$txt<br/>";
		//  }
		//$this->ws->getCellByColumnAndRow($x, $y)->getNumberFormat()->setFormatCode($myformat);
		//}

		//setCellValueByColumnAndRow($x,$y,$txt);



		if($align=='C')
			$this->ws->getStyleByColumnAndRow($x, $y)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		elseif($align=='R')
			$this->ws->getStyleByColumnAndRow($x, $y)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		else
			$this->ws->getStyleByColumnAndRow($x, $y)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


		/*}
		else{

		$EXCEL_HALIGN_GENERAL        = 0x00;
		$EXCEL_HALIGN_LEFT            = 0x01;
		$EXCEL_HALIGN_CENTRED        = 0x02;
		$EXCEL_HALIGN_RIGHT            = 0x03;
		$EXCEL_HALIGN_FILLED            = 0x04;
		$EXCEL_HALIGN_JUSITFIED        = 0x05;
		$EXCEL_HALIGN_SEL_CENTRED    = 0x06;    // centred across selection
		$EXCEL_HALIGN_DISTRIBUTED    = 0x07;    //
		if($align=='C')
		$align=$EXCEL_HALIGN_CENTRED;
		elseif($align=='R')
		$align=$EXCEL_HALIGN_RIGHT;
		else
		$align=$EXCEL_HALIGN_LEFT;
		//$this->wfont = new ExcelFont(ExcelFont::WEIGHT_NORMAL); 



		$this->wformat->setFont($this->wfont);
		$this->wformat->setAlignment($align);  
		if(strpos($pattern,".")!==false || strpos($pattern,"#")!==false){    

		$this->wformat->setFormatString($pattern);

		//         $this->wformat->setFont($this->wfont);  
		$this->ws->setDouble($x,$y-1,$txt,$this->wformat);
		}
		else{


		$this->ws->setAnsiString($x,$y-1,$txt,$this->wformat); //Mac OSX's iconv not able to convert char * to wchar_t* well.
		} 
		if($this->debughtml==true)
		echo "Reset Font/format to default<br/>";                                
		}  */

	}
	public function mergeCells($x1,$y1,$x2,$y2){
		//if($this->uselib==0){
		if($x2=="")$x2=$x1;
		if($y2=="")$y2=$y1;

		$this->ws->mergeCellsByColumnAndRow($x1,$y1,$x2, $y2);
		//}
		/*else{
		if($x2=="")$x2=0;
		if($y2=="")$y2=0;

		$this->ws->mergeCells($x1,$y1-1,($x2-$x1)+1, ($y2-$y1)+1);
		}  */
	}


	public function  SetFonts($x,$y,$font,$fontsize,$fontstyle){


		//if($this->uselib==0){
		//echo "phpexcel";
		$f=$this->ws->getStyleByColumnAndRow($x, $y)->getFont();

		$f->setName($font);

		$f->setSize(intVal($fontsize));

		if(strpos($fontstyle,'B')!==false)
			$f->setBold(true);
		else
			$f->setBold(false);

		//if(strpos($fontstyle,'U')!==false)
		//	$f->setUnderline(PHPExcel\PHPExcel_Style_Font::UNDERLINE_SINGLE);
		//else
		//	$f->setUnderline(PHPExcel_Style_Font::UNDERLINE_NONE);

		if(strpos($fontstyle,'I')!==false)
			$f->setItalic(true);
		else
			$f->setItalic(false);

		/*}else{
		//$this->ws->setFormat($this->blankformat);
		//$this->wformat= new ExcelCellFormat($this->wb);

		if(strpos($fontstyle,'B')!==false)
		$this->wfont=new ExcelFont(ExcelFont::WEIGHT_BOLD); 
		else    
		$this->wfont=new ExcelFont(ExcelFont::WEIGHT_NORMAL); 



		if(strpos($fontstyle,'I')!==false)
		$this->wfont->setItalic(true);
		else
		$this->wfont->setItalic(false);
		if(strpos($fontstyle,'U')!==false)
		$this->wfont->setUnderline(true);
		else
		$this->wfont->setUnderline(false);



		$this->wfont->setFontName($font);        
		$this->wfont->setFontSize($fontsize);


		//$this->ws->setAnsiString($x,$y-1,"$fontstyle",$this->wformat);
		/* 

		* 
		*     * 








		}    */

	}
	public function deleteEmptyRow($rowpos){
			for($l=1;$l<=$rowpos;$l++){

				$rh=$this->ws->getRowDimension($l)->getRowHeight();

				if($rh==1){
					$this->ws->removeRow($l,$l+1);


				}


			}
			// print_r($emptrowgroup);

	}
	public function SetTextColor($x,$y,$cl){
		//if($this->uselib==0){
		$this->ws->getStyleByColumnAndRow($x, $y)->getFont()->getColor()->setARGB("FF".$cl);
		//}else{
		/*
		EGA_BLACK    = 0,    // 000000H
		EGA_WHITE    = 1,    // FFFFFFH
		EGA_RED        = 2,    // FF0000H
		EGA_GREEN    = 3,    // 00FF00H
		EGA_BLUE    = 4,    // 0000FFH
		EGA_YELLOW    = 5,    // FFFF00H
		EGA_MAGENTA    = 6,    // FF00FFH
		EGA_CYAN    = 7        // 00FFFFH
		*/
		//         $this->wfont->setColor(0);
		//}

	} 

	public function SetFillColor($x,$y,$cl){
		if($this->uselib==0){
			$this->ws->getStyleByColumnAndRow($x,$y)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->ws->getStyleByColumnAndRow($x,$y)->getFill()->getStartColor()->setARGB('FF'.$cl);
		}else{

			/*
			EGA_BLACK    = 0,    // 000000H
			EGA_WHITE    = 1,    // FFFFFFH
			EGA_RED        = 2,    // FF0000H
			EGA_GREEN    = 3,    // 00FF00H
			EGA_BLUE    = 4,    // 0000FFH
			EGA_YELLOW    = 5,    // FFFF00H
			EGA_MAGENTA    = 6,    // FF00FFH
			EGA_CYAN    = 7        // 00FFFFH
			*/
			//     $this->wformat->setBackGround(1);



		}


	}
	public function generate($obj = NULL)
	{   
		$this->dbData = $this->getDbData();
		// exibe a tag
		parent::generate($this);
		return $this->arrayVariable;
	}
	public function out(){
		$instructions = JasperPHP\Pdf::getInstructions();
		JasperPHP\Pdf::clearInstructrions();
		$this->runInstructions($instructions);

	}

}
?>