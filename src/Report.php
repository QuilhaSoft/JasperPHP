<?php
namespace JasperPHP;
use JasperPHP; 
use JasperPHP\ado\TTransaction;
//use TTransaction;
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
class Report extends Element
{
    public $dbData;
    public $y_axis;
    public $currrentPage = 1 ;
    public $fontdir;
    public $arrayVariable;
    public $arrayfield;
    public $arrayParameter;
    public $arrayPageSetting;
    public $defaultFontSize  = 10;
    public $sql;
    public $print_expression_result;
    public $returnedValues = array();
    public $objElement;
    public $defaultFolder = 'app.jrxml';

    public function __construct($xmlFile = null,$param)
    {     
        $this->fontdir = "app.phpEx/Jsp/tcpdf/fonts";
        $xmlFile = str_ireplace(array('"'),array(''),$xmlFile);
        $xmlFile = file_get_contents($this->defaultFolder . DIRECTORY_SEPARATOR . $xmlFile);
        $keyword="<queryString>
        <![CDATA[";
        $xmlFile=  str_replace( $keyword, "<queryString><![CDATA[", $xmlFile );
        $xml =  simplexml_load_string( $xmlFile );
        $this->charge($xml,$param);
        $this->objElement = $this;
    }  
    public function charge($ObjElement,$param){

        $this->name = get_class($this);
        $this->objElement =  $ObjElement;
        // atribui o conteúdo do label
        $attributes = $ObjElement->attributes();
        foreach($attributes as $att => $value){
            $this->$att = $value; 
        }
        foreach($ObjElement as $obj=>$value){
            $obj = ($obj=='break')?'Breaker':$obj;
            $className = "JasperPHP\\".$obj;
            if(class_exists($className)){
                $this->add(new $className($value));
            }
        }
        $this->parameter_handler($ObjElement,$param);
        $this->field_handler($ObjElement);
        $this->variable_handler($ObjElement);
        $this->page_setting($ObjElement);
        $this->queryString_handler($ObjElement);
    }
    public function getDbData(){

        if ($conn = TTransaction::get())
        {
            // registra mensagem de log
            TTransaction::log($this->sql);

            // executa instrução de SELECT
            $result= $conn->Query($this->sql);
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }


    public function page_setting($xml_path) {
        $this->arrayPageSetting["orientation"]="P";
        $this->arrayPageSetting["name"]=$xml_path["name"];
        $this->arrayPageSetting["language"]=$xml_path["language"];
        $this->arrayPageSetting["pageWidth"]=$xml_path["pageWidth"];
        $this->arrayPageSetting["pageHeight"]=$xml_path["pageHeight"];
        if(isset($xml_path["orientation"])) {
            $this->arrayPageSetting["orientation"]=substr($xml_path["orientation"],0,1);
        }
        $this->arrayPageSetting["columnWidth"]=$xml_path["columnWidth"];
        $this->arrayPageSetting["leftMargin"]=$xml_path["leftMargin"];
        $this->arrayPageSetting["rightMargin"]=$xml_path["rightMargin"];
        $this->arrayPageSetting["topMargin"]=$xml_path["topMargin"];
        $this->y_axis=$xml_path["topMargin"];
        $this->arrayPageSetting["bottomMargin"]=$xml_path["bottomMargin"];
    }

    public function field_handler($xml_path) {
        foreach($xml_path->field as $field){
            $this->arrayfield[]=$field["name"];
        }
    }

    public function parameter_handler($xml_path,$param) {
        foreach($xml_path->parameter as $parameter){
            $paraName = (string)$parameter["name"];
            $this->arrayParameter[$paraName] = $param[$paraName];        
        }
    }

    public function variable_handler($xml_path) {

        foreach($xml_path->variable as $variable){
            $varName = (string)$variable["name"];
            $this->arrayVariable[$varName]=array("calculation"=>$variable["calculation"]."",
                "target"=>$variable->variableExpression ,
                "class"=>$variable["class"] ."",
                "resetType"=>$variable["resetType"]."",
                "resetGroup"=>$variable["resetGroup"]."",
                "initialValue"=>(string)$variable->initialValueExpression."",
                "incrementType"=>$variable['incrementType']
            );
        }

    }
    public function queryString_handler($xml_path) {
        $this->sql =$xml_path->queryString;
        if(isset($this->arrayParameter)) {
            foreach($this->arrayParameter as  $v => $a) {
                if(is_array($a)){
                    $this->sql = str_replace('$P{'.$v.'}', "(".implode(',',$a).")", $this->sql);
                }else{
                    $this->sql = str_replace('$P{'.$v.'}', $a, $this->sql);
                }
            }
        }
    }
    public function runInstructions($instructions){
        $pdf = JasperPHP\Pdf::get(); 
        $maxheight = null; 

        foreach($instructions as $arraydata){
            //$this->Rotate($arraydata["rotation"]);
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
            }
        }
    }  
    public function checkoverflow($obj) {
        $pdf = JasperPHP\Pdf::get();
        // var_dump($obj->children); 
        $txt = $obj['txt'];  
        $newfont=    $this->recommendFont($txt,null,null);

        //$pdf->SetFont($newfont,$pdf->getFontStyle(),$this->defaultFontSize);
        $this->print_expression($obj);
        $arraydata = $obj;
        $pdf->SetXY($arraydata["x"]+$this->arrayPageSetting["leftMargin"],$arraydata["y"]+$this->y_axis);
        if($this->print_expression_result==true) {
            // echo $arraydata["link"];
            if($arraydata["link"]) {
                //print_r($arraydata);

                //$this->debughyperlink=true;
                //  echo $arraydata["link"].",print:".$this->print_expression_result;
                $arraydata["link"]=$this->analyse_expression($arraydata["link"],"");
                //$this->debughyperlink=false;
            }
            //print_r($arraydata);


            if($arraydata["writeHTML"]==1) {
                $pdf->writeHTML($txt,true, 0, true, true);
                $pdf->Ln();
                /*if($this->currentband=='detail'){
                if($this->maxpagey['page_'.($pdf->getPage()-1)]=='')
                $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                else{
                if($this->maxpagey['page_'.($pdf->getPage()-1)]<$pdf->GetY())
                $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                }
                }*/

            }

            elseif($arraydata["poverflow"] == "false" && $arraydata["soverflow"] == "false") {
                if($arraydata["valign"]=="M")
                    $arraydata["valign"]="C";
                if($arraydata["valign"]=="")
                    $arraydata["valign"]="T";                


                while($pdf->GetStringWidth(utf8_decode(utf8_decode($txt))) >  $arraydata["width"]) { // aka a gambiarra da gambiarra funcionan assim nao mude a naão ser que de problema seu bosta
                    if($txt!=$pdf->getAliasNbPages() && $txt!=' '.$pdf->getAliasNbPages()){
                        $txt=substr($txt,0,-1);                                  
                    }
                } 

                $x=$pdf->GetX();
                $y=$pdf->GetY();
                $text=$this->formatText($txt, $arraydata["pattern"]);
                $pdf->Cell($arraydata["width"], $arraydata["height"],$text,
                    $arraydata["border"],"",$arraydata["align"],$arraydata["fill"],
                    $arraydata["link"],
                    0,true,"T",$arraydata["valign"]); 
                //$pdf->Ln();
                if($this->currentband=='detail'){
                    if($this->maxpagey['page_'.($pdf->getPage()-1)]=='')
                        $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                    else{
                        if($this->maxpagey['page_'.($pdf->getPage()-1)]<$pdf->GetY())
                            $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                    }
                }
            }
            elseif($arraydata["poverflow"]=="true") {
                if($arraydata["valign"]=="C")
                    $arraydata["valign"]="M";
                if($arraydata["valign"]=="")
                    $arraydata["valign"]="T";

                $x=$pdf->GetX();
                $yAfter = $pdf->GetY();
                //if($arraydata["link"])   echo $arraydata["linktarget"].",".$arraydata["link"]."<br/><br/>";
                $pdf->MultiCell($arraydata["width"], $arraydata["height"], $this->formatText($txt, $arraydata["pattern"]),$arraydata["border"] 
                    ,$arraydata["align"], $arraydata["fill"],1,'','',true,0,false,true,$maxheight);//,$arraydata["valign"]);
                if(($yAfter+$arraydata["height"])<=$this->arrayPageSetting["pageHeight"]){
                    $this->y_axis = $pdf->GetY()-20;
                }
                if( $pdf->balancetext=='' && $this->currentband=='detail'){
                    if($this->maxpagey['page_'.($pdf->getPage()-1)]=='')
                        $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                    else{
                        if($this->maxpagey['page_'.($pdf->getPage()-1)]<$pdf->GetY())
                            $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                    }
                }
                //$this->pageFooter();
                if($pdf->balancetext!='' ){
                    $this->continuenextpageText=array('width'=>$arraydata["width"], 'height'=>$arraydata["height"], 'txt'=>$pdf->balancetext,
                        'border'=>$arraydata["border"] ,'align'=>$arraydata["align"], 'fill'=>$arraydata["fill"],'ln'=>1,
                        'x'=>$x,'y'=>'','reset'=>true,'streth'=>0,'ishtml'=>false,'autopadding'=>true);
                    $pdf->balancetext='';
                    $this->forcetextcolor_b=$this->textcolor_b;
                    $this->forcetextcolor_g=$this->textcolor_g;
                    $this->forcetextcolor_r=$this->textcolor_r;
                    $this->forcefillcolor_b=$this->fillcolor_b;
                    $this->forcefillcolor_g=$this->fillcolor_g;
                    $this->forcefillcolor_r=$this->fillcolor_r;
                    if($this->continuenextpageText)
                        $this->printlongtext($pdf->getFontFamily(),$pdf->getFontStyle(),$pdf->getFontSize());

                }          
            }
            elseif($arraydata["soverflow"]=="true") {

                if($arraydata["valign"]=="M")
                    $arraydata["valign"]="C";
                if($arraydata["valign"]=="")
                    $arraydata["valign"]="T"; 

                $pdf->Cell($arraydata["width"], $arraydata["height"],  $this->formatText($txt, $arraydata["pattern"]),$arraydata["border"],"",$arraydata["align"],$arraydata["fill"],$arraydata["link"]."",0,true,"T",
                    $arraydata["valign"]);
                $pdf->Ln();
                if($this->currentband=='detail'){
                    if($this->maxpagey['page_'.($pdf->getPage()-1)]=='')
                        $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                    else{
                        if($this->maxpagey['page_'.($pdf->getPage()-1)]<$pdf->GetY())
                            $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                    }
                }
            }
            else {
                //MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0) {    
                $pdf->MultiCell($arraydata["width"], $arraydata["height"], $this->formatText($txt, $arraydata["pattern"]), $arraydata["border"], 
                    $arraydata["align"], $arraydata["fill"],1,'','',true,0,true,true,$maxheight);
                if( $pdf->balancetext=='' && $this->currentband=='detail'){
                    if($this->maxpagey['page_'.($pdf->getPage()-1)]=='')
                        $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                    else{
                        if($this->maxpagey['page_'.($pdf->getPage()-1)]<$pdf->GetY())
                            $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                    }
                }
                if($pdf->balancetext!=''){
                    $this->continuenextpageText=array('width'=>$arraydata["width"], 'height'=>$arraydata["height"], 'txt'=>$pdf->balancetext,
                        'border'=>$arraydata["border"] ,'align'=>$arraydata["align"], 'fill'=>$arraydata["fill"],'ln'=>1,
                        'x'=>$x,'y'=>'','reset'=>true,'streth'=>0,'ishtml'=>false,'autopadding'=>true);
                    $pdf->balancetext='';
                    $this->forcetextcolor_b=$this->textcolor_b;
                    $this->forcetextcolor_g=$this->textcolor_g;
                    $this->forcetextcolor_r=$this->textcolor_r;
                    $this->forcefillcolor_b=$this->fillcolor_b;
                    $this->forcefillcolor_g=$this->fillcolor_g;
                    $this->forcefillcolor_r=$this->fillcolor_r;
                    $this->gotTextOverPage=true;
                    if($this->continuenextpageText)
                        $this->printlongtext($pdf->getFontFamily(),$pdf->getFontStyle(),$pdf->getFontSize());

                }   
            }
        }
        $this->print_expression_result=false;   
    }
    public function print_expression($data) {
        $expression=$data["printWhenExpression"];
        $this->print_expression_result=false;
        if($expression!=""){
            //$expression=$this->analyse_expression($expression);
            eval('if('.$expression.'){$this->print_expression_result=true;}');
        }
        else
            $this->print_expression_result=true;


    }
    public function formatText($txt,$pattern) {
        $nome_meses = array('Janeiro','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
        if($pattern=="###0")
            return number_format($txt,0,"","");
        elseif($pattern=="#,##0")
            return number_format($txt,0,",",".");
        elseif($pattern=="###0.0")
            return number_format($txt,1,",","");
        elseif($pattern=="#.##0.0" || $pattern=="#,##0.0;-#,##0.0")
            return number_format($txt,1,",",".");
        elseif($pattern=="###0.00" || $pattern=="###0.00;-###0.00")
            return number_format($txt,2,",","");
        elseif($pattern=="#,##0.00" || $pattern=="#,##0.00;-#,##0.00")
            return number_format($txt,2,",",".");
        elseif($pattern=="###0.00;(###0.00)")
            return ($txt<0 ? "(".number_format(abs($txt),2,",","").")" : number_format($txt,2,",",""));
        elseif($pattern=="#,##0.00;(#,##0.00)")
            return ($txt<0 ? "(".number_format(abs($txt),2,",",".").")" : number_format($txt,2,",","."));
        elseif($pattern=="#,##0.00;(-#,##0.00)")
            return ($txt<0 ? "(".number_format($txt,2,",",".").")" : number_format($txt,2,",","."));
        elseif($pattern=="###0.000")
            return number_format($txt,3,",","");
        elseif($pattern=="#,##0.000")
            return number_format($txt,3,",",".");
        elseif($pattern=="#,##0.0000")
            return number_format($txt,4,",",".");
        elseif($pattern=="###0.0000")
            return number_format($txt,4,",","");
            
        elseif($pattern=="xx/xx"  && $txt !="")
            return substr($txt,0,2)."/".substr($txt,2,2);

        elseif(($pattern=="dd/MM/yyyy" || $pattern=="ddMMyyyy") && $txt !="")
            return date("d/m/Y",strtotime($txt));
        elseif($pattern=="MM/dd/yyyy" && $txt !="")
            return date("m/d/Y",strtotime($txt));
        elseif($pattern=="dd/MM/yy" && $txt !="")
            return date("d/m/y",strtotime($txt));
        elseif($pattern=="yyyy/MM/dd" && $txt !="")
            return date("Y/m/d",strtotime($txt));
        elseif($pattern=="dd-MMM-yy" && $txt !="")
            return date("d-M-Y",strtotime($txt));
        elseif($pattern=="dd-MMM-yy" && $txt !="")
            return date("d-M-Y",strtotime($txt));
        elseif($pattern=="dd/MM/yyyy h.mm a" && $txt !="")
            return date("d/m/Y h:i a",strtotime($txt));
        elseif($pattern=="dd/MM/yyyy HH.mm.ss" && $txt !="")
            return date("d-m-Y H:i:s",strtotime($txt));
        elseif($pattern=="H:m:s" && $txt !="")
            return date("H:i:s",strtotime($txt));
        elseif(($pattern=="d F yyyy" ||$pattern=="dFyyyy") && $txt !="")
            return date("d ",strtotime($txt))." de ".$nome_meses[date("n",strtotime($txt))]." de ".date("Y",strtotime($txt));
        elseif($pattern!="" && $txt !=""){
            return date($pattern,strtotime($txt));
        }else
            return $txt;
    }
    /* public function analyse_expression($data,$isPrintRepeatedValue="true") {
    //echo $data."<br/>";
    $tmpplussymbol='|_plus_|';
    $pointerposition=$this->global_pointer+$this->offsetposition;
    $i=0;
    $backcurl='___';
    $singlequote="|_q_|";
    $doublequote="|_qq_|";
    $fm=str_replace('{',"_",$data);
    $fm=str_replace('}',$backcurl,$fm);

    //$fm=str_replace('$V_REPORT_COUNT',$this->report_count,$fm);
    $isstring=false;


    //        if($this->report_count>10 && $data=='$F{qty}' || $data=='$V{qty2}')  {
    //               echo "$data =  $fm<br/>";
    //             }
    if($this->arrayVariable){
    foreach($this->arrayVariable as $vv=>$av){
    $i++;
    $vv=str_replace('$V{',"",$vv);
    $vv=str_replace('}',$backcurl,$vv);
    $vv=str_replace("'", $singlequote,$vv);
    $vv=str_replace('"', $doublequote,$vv);
    //if(strpos($fm,'REPORT_COUNT')){
    //      echo $fm;die;}
    //echo $vv.' to become '.$this->grouplist[1]["name"]."_COUNT <br/  >";
    //           if($vv==$this->grouplist[0]["name"]."_COUNT" ){
    //               
    //             $fm=str_replace('$V_'.$vv."_COUNT",39992,$fm1);
    //             //echo 39992 . "<br/>";
    //           }
    //           elseif($vv==$this->grouplist[1]["name"]."_COUNT"){
    //             $fm=str_replace('$V_'.$vv."_COUNT",$this->group_count[$this->grouplist[1]["name"]],$fm1);
    //             //echo 39992 . "<br/>";
    //           }
    //           elseif($vv==$this->grouplist[2]["name"]."_COUNT"){
    //               $fm=str_replace('$V_'.$vv."_COUNT",$this->group_count[$this->grouplist[2]["name"]],$fm1);
    //           }
    //           elseif($vv==$this->grouplist[3]["name"]."_COUNT"){
    //               $fm=str_replace('$V_'.$vv."_COUNT",$this->group_count[$this->grouplist[3]["name"]],$fm1);
    //           }
    if(strpos($fm,'_COUNT')!==false){
    $fm=str_replace('$V_'.$this->grouplist[0]["name"].'_COUNT'.$backcurl,($this->group_count[$this->grouplist[0]["name"]]-1),$fm);
    $fm=str_replace('$V_'.$this->grouplist[1]["name"].'_COUNT'.$backcurl,($this->group_count[$this->grouplist[1]["name"]]-1),$fm);
    $fm=str_replace('$V_'.$this->grouplist[2]["name"].'_COUNT'.$backcurl,($this->group_count[$this->grouplist[2]["name"]]-1),$fm);
    $fm=str_replace('$V_'.$this->grouplist[3]["name"].'_COUNT'.$backcurl,($this->group_count[$this->grouplist[3]["name"]]-1),$fm);


    }
    else{

    if($av["ans"]!="" && is_numeric($av["ans"]) && ($this->left($av["ans"],1)||$this->left($av["ans"],1)=='-' )>0){
    $av["ans"]=str_replace("+",$tmpplussymbol,$av["ans"]);
    $fm=str_replace('$V_'.$vv.$backcurl,$av["ans"],$fm);
    }
    else{
    $av["ans"]=str_replace("+",$tmpplussymbol,$av["ans"]);
    $fm=str_replace('$V_'.$vv.$backcurl,"'".$av["ans"]."'",$fm);
    $isstring=true;
    }
    }
    }
    }


    $fm=str_replace('$V_REPORT_COUNT'.$backcurl,$this->report_count,$fm);
    /*foreach($this->arrayParameter as  $pv => $ap) {
    $ap=str_replace("+",$tmpplussymbol,$ap);
    $ap=str_replace("'", $singlequote,$ap);
    $ap=str_replace('"', $doublequote,$ap);
    if(is_numeric($ap)&&$ap!=''&& ($this->left($ap,1)>0 || $this->left($ap,1)=='-')){
    $fm = str_replace('$P_'.$pv.$backcurl, $ap,$fm);
    }
    else{
    $fm = str_replace('$P_'.$pv.$backcurl, "'".$ap."'",$fm);
    $isstring=true;
    }
    }

    //     print_r($this->arrayfield);
    if($this->arrayVariable){
    foreach($this->arrayfield as $af){
    $tmpfieldvalue=str_replace("+",$tmpplussymbol,$this->arraysqltable[$pointerposition][$af.""]);
    $tmpfieldvalue=str_replace("'", $singlequote,$tmpfieldvalue);
    $tmpfieldvalue=str_replace('"', $doublequote,$tmpfieldvalue);
    if(is_numeric($tmpfieldvalue) && $tmpfieldvalue!="" && ($this->left($tmpfieldvalue,1)>0||$this->left($tmpfieldvalue,1)=='-')){
    $fm =str_replace('$F_'.$af.$backcurl,$tmpfieldvalue,$fm);

    }
    else{
    $fm =str_replace('$F_'.$af.$backcurl,"'".$tmpfieldvalue."'",$fm);
    $isstring=true;
    }

    }
    }

    if($fm=='')
    return "";
    else
    {


    //echo $fm."<br/>";


    //              $fm=str_replace('+',".",$fm);
    // echo $fm."<br/>";
    if(strpos($fm, '"')!==false)
    $fm=str_replace('+'," . ",$fm);
    if(strpos($fm, "'")!==false)
    $fm=str_replace('+'," . ",$fm);


    $fm=str_replace($tmpplussymbol,"+",$fm);


    $fm=str_replace('$this->PageNo()',"''",$fm);


    $fm=str_replace($singlequote,"\'" ,$fm);
    $fm=str_replace( $doublequote,'"',$fm);

    if((strpos('"',$fm)==false) || (strpos("'",$fm)==false)){
    $fm=str_replace('--', '- -', $fm);
    $fm=str_replace('++', '+ +', $fm);
    }
    /* if(strpos($fm, "124.99")){

    echo $fm."<br/><br/>";
    }

    eval("\$result= ".$fm.";");

    /*if(strpos($fm, "458.21")){

    echo $fm.":$result<br/><br/>";
    }



    //if($this->debughyperlink==true) 

    return $result;

    }

    } */        
    public function variables_calculation($obj,$row = 'StdClass') {

        foreach($this->arrayVariable as $k=>$out) {
            $this->variable_calculation($k,$out,$row);
        } 
    }
    public function setReturnVariables($subReportTag,$arrayVariablesSubReport){
        if($subReportTag->returnValues){
            foreach($subReportTag->returnValues as $key=>$value){
                $val = (array)$value;
                $subreportVariable = (string)$value['subreportVariable'];
                $toVariable        = (string)$value['toVariable'] ;
                $val['ans'] = $arrayVariablesSubReport[$subreportVariable]['ans'];
                $val['calculation'] = (string)$value['calculation'];
                $val['class'] = (string)$value['class'];
                $this->returnedValues[$toVariable] = $val;
            }
            $this->returnedValues_calculation();
        }

    }
    public function returnedValues_calculation() {

        foreach($this->returnedValues as $k=>$out) {
            $out['target'] = "\$F{".$k."}";
            $subreportVariable = (string)$out['subreportVariable'];
            $toVariable        = (string)$out['toVariable'] ;
            $row = array();
            $row[$k] = $out['ans'];
            $this->variable_calculation($k,$out,(object)$row);
        } 
    }
    public function getValOfVariable($variable,$text){
        $val = $this->arrayVariable[$variable];
        if(preg_match_all("/V{".$variable."}\.toString/",$text,$matchesV)>0){
            return str_ireplace(array('$V{'.$variable.'}.toString()'),array(number_format($val['ans'],2,',','.')),$text);
        } elseif(preg_match_all("/V{".$variable."}\.numberToText/",$text,$matchesV)>0){
            return str_ireplace(array('$V{'.$variable.'}.numberToText()'),array($this->numberToText($val['ans'],true)),$text); 
        }elseif(preg_match_all("/V{".$variable."}\.(\w+)/",$text,$matchesV)>0){
            $funcName  = $matchesV[1][0];
            return str_ireplace(array('$V{'.$variable.'}'),array(call_user_func_array(array($this,$funcName),array($val,true))),$text);
        } elseif($variable == "MASTER_TOTAL_PAGES"){
            return str_ireplace(array('$V{'.$variable.'}'),array('$this->getAliasNbPages()'),$text);
        }elseif($variable == "PAGE_NUMBER" || $variable == "MASTER_CURRENT_PAGE"){
            return str_ireplace(array('$V{'.$variable.'}'),array('$this->getPageNo()'),$text);; 
        }else{
            return str_ireplace(array('$V{'.$variable.'}'),array($val['ans']),$text); 
        }
    }
    public function getValOfField($field,$row,$text){
        $val = $row->$field;
        if(preg_match_all("/F{".$field."}\.toString/",$text,$matchesV)>0){
            return str_ireplace(array('$F{'.$field.'}.toString()'),array(number_format($val,2,',','.')),$text);
        } elseif(preg_match_all("/F{".$field."}\.numberToText/",$text,$matchesV)>0){
            return str_ireplace(array('$F{'.$field.'}.numberToText()'),array($this->numberToText($val,true)),$text); 
        }elseif(preg_match_all("/F{".$field."}\.(\w+)\((\w+)\)/",$text,$matchesV)>0){
            $funcName  = $matchesV[1][0];
            return str_ireplace(array('$'.$matchesV[0][0]),array(call_user_func_array(array($this,$funcName),array($val,$matchesV[2][0]))),$text);
        }elseif(preg_match_all("/F{".$field."}\.(\w+)/",$text,$matchesV)>0){
            $funcName  = $matchesV[1][0];
            return str_ireplace(array('$'.$matchesV[0][0]."()"),array(call_user_func_array(array($this,$funcName),array($val,true))),$text);

        }else{
            return str_ireplace(array('$F{'.$field.'}'),array(utf8_encode($val)),$text); 
        }
    }
    public function variable_calculation($k,$out,$row){
        preg_match_all("/P{(\w+)}/",$out['target'] ,$matchesP);
        if($matchesP){
            foreach($matchesP[1] as $macthP){
                $out['target'] = str_ireplace(array('$P{'.$macthP.'}'),array($this->arrayParameter[$macthP]),$out['target']); 
            } 
        }
        preg_match_all("/V{(\w+)}/",$out['target'] ,$matchesV);
        if($matchesV){
            foreach($matchesV[1] as $macthV){
                $defVal = $this->arrayVariable[$macthV]['ans']!=''?$this->arrayVariable[$macthV]['ans']:$this->arrayVariable[$macthV]['initialValue'];
                $out['target'] = str_ireplace(array('$V{'.$macthV.'}'),array($this->arrayVariable[$macthV]['ans']),$out['target']); 
            }
        }
        preg_match_all("/F{(\w+)}/",$out['target'] ,$matchesF);
        if($matchesF){
            foreach($matchesF[1] as $macthF){ 
                $out['target'] = $this->getValOfField($macthF,$row,$out['target']);//str_ireplace(array('$F{'.$macthF.'}'),array(utf8_encode($row->$macthF)),$out['target']); 
            }
        }
        if(preg_match('/(\d+)(?:\s*)([\+\-\*\/])(?:\s*)/', $out['target'], $matchesMath)>0 && $this->arrayVariable['htmlData']['class'] != 'HTMLDATA' ){
            $mathValue = eval('return ('.$out['target'].');');
        }

        $value=$this->arrayVariable[$k]["ans"];
        $newValue = (isset($mathValue))?$mathValue:$out['target'];
        //   echo $out['resetType']. "<br/><br/>";
        switch($out["calculation"]) {
            case "Sum":
                if($out['resetType']=='' || $out['resetType']=='None' ){
                    if(isset($this->arrayVariable[$k]['class'])&&$this->arrayVariable[$k]['class']=="java.sql.Time") {
                        //    foreach($this->arraysqltable as $table) {
                        $value=$this->time_to_sec($value);

                        $value+=$this->time_to_sec($newValue);
                        //$sum=$sum+substr($table["$out[target]"],0,2)*3600+substr($table["$out[target]"],3,2)*60+substr($table["$out[target]"],6,2);
                        // }
                        //$sum= floor($sum / 3600).":".floor($sum%3600 / 60);
                        //if($sum=="0:0"){$sum="00:00";}
                        $value=$this->sec_to_time($value);
                    }
                    else {
                        //resetGroup
                        // foreach($this->arraysqltable as $table) {

                        $value+=$newValue;
                        //echo "k=$k, $value<br/>";
                        //      $table[$out["target"]];
                        //   }
                    }

                }// finisish resettype=''
                elseif($out['resetType']=='Group') //reset type='group'
                {


                    //                       print_r($this->grouplist);
                    //                       echo "<br/>";
                    //                       echo $out['resetGroup'] ."<br/>";
                    //                       //                        if( $this->arraysqltable[$this->global_pointer][$this->group_pointer]!=$this->arraysqltable[$this->global_pointer-1][$this->group_pointer])
                    //                        if( $this->arraysqltable[$this->global_pointer][$this->group_pointer]!=$this->arraysqltable[$this->global_pointer-1][$this->group_pointer])
                    //                           $value=0;
                    //            
                    if($this->groupnochange>=0){


                        //     for($g=$this->groupnochange;$g<4;$g++){
                        //        $value=0;    
                        //                                  $this->arrayVariable[$k]["ans"]=0;
                        //                                echo $this->grouplist[$g]["name"].":".$this->groupnochange."<br/>";
                        // }
                    }
                    //    echo $this->global_pointer.",".$this->group_pointer.",".$this->arraysqltable[$this->global_pointer][$this->group_pointer].",".$this->arraysqltable[$this->global_pointer-1][$this->group_pointer].",".$this->arraysqltable[$rowno]["$out[target]"];
                    if(isset($this->arrayVariable[$k]['class'])&&$this->arrayVariable[$k]['class']=="java.sql.Time") {
                        $value+=$this->time_to_sec($newValue);
                        //$sum= floor($sum / 3600).":".floor($sum%3600 / 60);
                        //if($sum=="0:0"){$sum="00:00";}
                        $value=$this->sec_to_time($value);
                    }
                    else {

                        $value+=$newValue;


                    }

                }


                $this->arrayVariable[$k]["ans"]=$value;

                //      echo ",$value<br/>";
                break;
            case "Average":


                if($out['resetType']==''|| $out['resetType']=='None' ){
                    if(isset($this->arrayVariable[$k]['class'])&&$this->arrayVariable[$k]['class']=="java.sql.Time") {
                        $value=$this->time_to_sec($value);
                        $value+=$this->time_to_sec($newValue);
                        $value=$this->sec_to_time($value);
                    }
                    else {
                        $value=($value*($this->report_count-1)+$newValue)/$this->report_count;
                    }

                }// finisish resettype=''
                elseif($out['resetType']=='Group') //reset type='group'
                {
                    if($this->groupnochange>=0){
                    }
                    if(isset($this->arrayVariable[$k]['class'])&&$this->arrayVariable[$k]['class']=="java.sql.Time") {
                        $value+=$this->time_to_sec($newValue);
                        $value=$this->sec_to_time($value);
                    }
                    else { 
                        $previousgroupcount=$this->group_count[$out['resetGroup']]-2;
                        $newgroupcount=$this->group_count[$out['resetGroup']]-1;
                        $previoustotal=$value*$previousgroupcount;
                        $newtotal=$previoustotal+$newValue;
                        $value=($newtotal)/$newgroupcount;
                    }

                }


                $this->arrayVariable[$k]["ans"]=$value;

                break;
            case "DistinctCount":
                break;
            case "Lowest":

                foreach($this->dbData as $rowData) {
                    $lowest=$rowData->$out["target"];
                    if($rowData->$out["target"]<$lowest) {
                        $lowest=$rowData->$out["target"];
                    }
                    $this->arrayVariable[$k]["ans"]=$lowest;
                }
                break;
            case "Highest":
                $out["ans"]=0;
                foreach($this->arraysqltable as $table) {
                    if($rowData->$out["target"]>$out["ans"]) {
                        $this->arrayVariable[$k]["ans"]=$rowData->$out["target"];
                    }
                }
                break;
                //### A Count for groups, as a variable. Not tested yet, but seemed to work in print_r()                    
            case "Count":
                $value=$this->arrayVariable[$k]["ans"];
                if( $this->arraysqltable[$this->global_pointer][$this->group_pointer]!=$this->arraysqltable[$this->global_pointer-1][$this->group_pointer])
                    $value=0;
                $value++;
                $this->arrayVariable[$k]["ans"]=$value;
                break;
                //### End of modification
            case "":
                // $out["target"]=0;
                if(strpos( $out["target"], "_COUNT")==-1)
                    $this->arrayVariable[$k]["ans"]=$this->analyse_expression( $newValue, true);
                else
                    $this->arrayVariable[$k]["ans"]=$newValue;
                //                     $out["target"]= $this->analyse_expression( $out['target'], true);

                //other cases needed, temporary leave 0 if not suitable case
                break;

        }
    }

    public function getPageNo(){
        $pdf = JasperPHP\Pdf::get();
        return $pdf->getPage(); 
    }

    public function getAliasNbPages(){
        $pdf = JasperPHP\Pdf::get();
        return $pdf->getNumPages(); 
    }


    public function updatePageNo($s) {
        $pdf = JasperPHP\Pdf::get();
        return str_replace('$this->PageNo()', $pdf->PageNo(),$s);
    }
    function right($value, $count) {

        return substr($value, ($count*-1));

    }

    function left($string, $count) {
        return substr($string, 0, $count);
    }

    public function Rotate($type, $x=-1, $y=-1)
    {
        $pdf = JasperPHP\Pdf::get();
        if($type=="")
            $angle=0;
        elseif($type=="Left")
            $angle=90;
        elseif($type=="Right")
            $angle=270;
        elseif($type=="UpsideDown")
            $angle=180;

        if($x==-1)
            $x=$pdf->getX();
        if($y==-1)
            $y=$pdf->getY();
        if($this->angle!=0)
            $pdf->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$pdf->k;
            $cy=($pdf->h-$y)*$pdf->k;
            $pdf->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }
    function numberToText($valor = 0, $maiusculas = false) {

        $singular = array("centavo", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão"); 
        $plural = array("centavos", "", "mil", "milhões", "bilhões", "trilhões", 
            "quatrilhões"); 

        $c = array("", "cem", "duzentos", "trezentos", "quatrocentos", 
            "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos"); 
        $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", 
            "sessenta", "setenta", "oitenta", "noventa"); 
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", 
            "dezesseis", "dezesete", "dezoito", "dezenove"); 
        $u = array("", "um", "dois", "tres", "quatro", "cinco", "seis", 
            "sete", "oito", "nove"); 

        $z = 0; 
        $rt = "";

        $valor = number_format($valor, 2, ".", "."); 
        $inteiro = explode(".", $valor); 
        for($i=0;$i<count($inteiro);$i++) 
            for($ii=strlen($inteiro[$i]);$ii<3;$ii++) 
                $inteiro[$i] = "0".$inteiro[$i]; 

        $fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2); 
        for ($i=0;$i<count($inteiro);$i++) { 
            $valor = $inteiro[$i]; 
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]]; 
            $rd = ($valor[1] < 2) ? "" : $d[$valor[1]]; 
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : ""; 

            $r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && 
                $ru) ? " e " : "").$ru; 
            $t = count($inteiro)-1-$i; 
            $r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : ""; 
            if ($valor == "000")$z++; elseif ($z > 0) $z--; 
            if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t]; 
            if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && 
                ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r; 
        } 

        if(!$maiusculas){ 
            return($rt ? $rt : "zero"); 
        } else { 
            if ($rt) $rt=str_ireplace(" E "," e ",ucwords($rt));
            return (($rt) ? ($rt) : "Zero"); 
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