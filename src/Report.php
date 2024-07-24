<?php

namespace JasperPHP;

use JasperPHP;
use JasperPHP\ado\TTransaction;
//use TTransaction;

/**
 * classe Report
 * classe para construção de relatorio
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 *
 *
 * 2015.03.11 -- criação
 * */
class Report extends Element {
    private $name;
    private $height;
    private $splitType;
    private $radius;
    private $scaleImage;
    private $y_axis;
	
    public static $defaultFolder = 'app.jrxml';
    public static $locale = 'en_us';
    public static $dec_point=".";
    public static $thousands_sep=",";
    public static $columnHeaderRepeat=false;
    public static $proccessintructionsTime = "after"; // after : process intructions after generate all intrucions / inline : process intrucions after gerenate each detail
    public $dbData;
    public $pageChanged;
    public $arrayVariable;
    public $arrayfield;
    public $arrayParameter;
    public $arrayProperty;
    public $arrayPageSetting;
    public $arrayGroup;
    public $sql;
    public $print_expression_result;
    public $returnedValues = array();
    public $objElement;
    public $rowData;
    public $lastRowData;
    public $arrayStyles;

    public function __construct($xmlFile, $param) {
        if (file_exists(self::$defaultFolder . DIRECTORY_SEPARATOR . $xmlFile)) {
            $xmlFile = file_get_contents(self::$defaultFolder . DIRECTORY_SEPARATOR . $xmlFile);
        } elseif (file_exists($xmlFile)) {
            $xmlFile = file_get_contents($xmlFile);
        }else{
            throw new Exception("File {$xmlFile} not found!!");
        }
        $keyword = "<queryString>
        <![CDATA[";
        $xmlFile = str_replace($keyword, "<queryString><![CDATA[", $xmlFile);
        $xml = simplexml_load_string($xmlFile,null,LIBXML_NOCDATA);
        $this->charge($xml, $param);
        //$this->objElement = $xml;
    }

    public function charge($ObjElement, $param) {

        $this->name = get_class($this);
        $this->objElement = $ObjElement;

        // atribui o conteúdo do label
        $attributes = $ObjElement->attributes;
        //var_dump($attributes);
        foreach ($attributes as $att => $value) {
            $this->$att = $value;
        }
        foreach ($ObjElement as $obj => $value) {
            $obj = ($obj == 'break') ? 'Breaker' : $obj;
            $className = "JasperPHP\\" . ucfirst($obj);
            // echo $className."|";
            if(ucfirst($obj)=='Style'){
            $this->addStyle($value); 
            }
            if (class_exists($className)) {
                // echo $className."%".CHR(10);
                $this->add(new $className($value));
            }
        }
        $this->parameter_handler($ObjElement, $param);
        $this->property_handler($ObjElement, $param);
        $this->field_handler($ObjElement);
        $this->variable_handler($ObjElement);
        $this->page_setting($ObjElement);
        $this->queryString_handler($ObjElement);
        $this->group_handler($ObjElement);
    }

    public function getDbData() {

        if(array_key_exists('net.sf.jasperreports.data.adapter',$this->arrayProperty)){
            $connectionName = explode('.',$this->arrayProperty['net.sf.jasperreports.data.adapter'])[1];
            $result = \Illuminate\Support\Facades\DB::connection($connectionName)->select($this->sql);
            $arrayVariable = isset($this->arrayVariable) ? $this->arrayVariable : array();
            $recordObject = array_key_exists('recordObj', $arrayVariable) ? $this->arrayVariable['recordObj']['initialValue'] : "stdClass";
            if($recordObject != 'stdClass'){
                $result  = $recordObject::hydrate($result);
            }
            $this->rowData = $result[0];
            return $result;

        }elseif ($conn = TTransaction::get()) {
            // registra mensagem de log
            TTransaction::log($this->sql);

            // executa instrução de SELECT
            $result = $conn->Query($this->sql);
            $arrayVariable = isset($this->arrayVariable) ? $this->arrayVariable : array();
            $recordObject = array_key_exists('recordObj', $arrayVariable) ? $this->arrayVariable['recordObj']['initialValue'] : "stdClass";

            $this->rowData = $result->fetchObject($recordObject);
            return $result;
        } else {
            // se não tiver transação, retorna uma exceção
            throw new Exception('No transaction!!');
        }
    }
	
	public function getDbDataQuery($sql) {

        if ($conn = JasperPHP\ado\TTransaction::get()) {
            // registra mensagem de log
            JasperPHP\ado\TTransaction::log($sql);

            // executa instrução de SELECT
            $result = $conn->Query($sql);
            $rowData = $result->fetchAll(\PDO::FETCH_CLASS);
            return $rowData;
        } else {
            // se não tiver transação, retorna uma exceção
            throw new Exception('No transaction!!');
        }
    }

    public function page_setting($xml_path) {
        $this->arrayPageSetting["orientation"] = "P";
        $this->arrayPageSetting["name"] = $xml_path["name"];
        $this->arrayPageSetting["language"] = $xml_path["language"];
        $this->arrayPageSetting["pageWidth"] = $xml_path["pageWidth"];
        $this->arrayPageSetting["pageHeight"] = $xml_path["pageHeight"];
        if (isset($xml_path["orientation"])) {
            $this->arrayPageSetting["orientation"] = mb_substr($xml_path["orientation"], 0, 1);
        }
        $this->arrayPageSetting["columnWidth"] = $xml_path["columnWidth"];
        $this->arrayPageSetting["columnCount"] = $xml_path["columnCount"];
        $this->arrayPageSetting["CollumnNumber"] = 1;
        $this->arrayPageSetting["leftMargin"] = $xml_path["leftMargin"];
        $this->arrayPageSetting["defaultLeftMargin"] = $xml_path["leftMargin"];
        $this->arrayPageSetting["rightMargin"] = $xml_path["rightMargin"];
        $this->arrayPageSetting["topMargin"] = $xml_path["topMargin"];
        $this->y_axis = $xml_path["topMargin"];
        $this->arrayPageSetting["bottomMargin"] = $xml_path["bottomMargin"];
    }

    public function field_handler($xml_path) {
        foreach ($xml_path->field as $field) {
            $this->arrayfield[] = $field["name"];
        }
    }

    public function parameter_handler($xml_path, $param) {
        $this->arrayParameter = array();
        if ($xml_path->parameter) {
            foreach ($xml_path->parameter as $parameter) {
                $paraName = (string) $parameter["name"];
                $this->arrayParameter[$paraName] = array_key_exists($paraName, $param) ? $param[$paraName] : '';
            }
        } else {
            $this->arrayParameter = array();
        }
    }    
    
    public function property_handler($xml_path, $param) {
        $this->arrayProperty = array();
        if ($xml_path->property) {
            foreach ($xml_path->property as $property) {
                $paraName = (string) $property["name"];
                $this->arrayProperty[$paraName] = (string)$property['value'];
            }
        } else {
            $this->arrayProperty = array();
        }
    }

    public function variable_handler($xml_path) {
        $this->arrayVariable = array();
        foreach ($xml_path->variable as $variable) {
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
    }

    public function group_handler($xml_path) {
        $this->arrayGroup = array();
        foreach ($xml_path->group as $group) {

            $groupName = (string) $group["name"];
            $this->arrayGroup[$groupName] = $group;
            $group->addAttribute('resetVariables', 'false');
        }
    }

	public function prepareSql($sql, $arrayParameter=array()){
		if (isset($arrayParameter) && !empty($arrayParameter)) {
                foreach ($arrayParameter as $v => $a) {
                    if (is_array($a)) {
                        foreach ($a as $x) {
                            // se for um inteiro
                            if (is_integer($x)) {
                                $foo[] = $x;
                            } else if (is_string($x)) {
                                // se for string, adiciona aspas
                                $foo[] = "'$x'";
                            }
                        }
                        // converte o array em string separada por ","
                        $result = '(' . implode(',', $foo) . ')';
                        $sql = str_replace('$P{' . $v . '}', $result, $sql);
                    } else {
                        /* if (is_integer($a))
                          {
                          $x = $a ;
                          }
                          else if (is_string($a))
                          {
                          // se for string, adiciona aspas
                          $x= "'$a'";
                          } */
                        $sql = str_replace('$P{' . $v . '}', $a, $sql);
                        $sql = str_replace('$P!{' . $v . '}', $a, $sql);
                    }
                }
            }
		return $sql;
	}
	
    public function queryString_handler($xml_path) {
        //var_dump($xml_path);
        $this->sql = (string) $xml_path->queryString;
        if (strlen(trim($xml_path->queryString)) > 0) {

            if (isset($this->arrayParameter)) {
				$this->sql=$this->prepareSql($this->sql,$this->arrayParameter);
            }
        }
    }

    public function variables_calculation($obj, $row = 'StdClass') {
        if ($this->arrayVariable) {
            foreach ($this->arrayVariable as $k => $out) {
                $this->variable_calculation($k, $out, $row);
            }
        }
        if($this->pageChanged == true){
            $this->pageChanged = false;
        }
    }

    public function setReturnVariables($subReportTag, $arrayVariablesSubReport) {
        if ($subReportTag->returnValues) {
            foreach ($subReportTag->returnValues as $key => $value) {
                $val = (array) $value;
                $subreportVariable = (string) $value['subreportVariable'];
                $toVariable = (string) $value['toVariable'];
                $ans = (array_key_exists('ans', $arrayVariablesSubReport[$subreportVariable])) ? $arrayVariablesSubReport[$subreportVariable]['ans'] : '';
                $val['ans'] = $ans;
                $val['calculation'] = (string) $value['calculation'];
                $val['class'] = (string) $value['class'];
                $this->returnedValues[$toVariable] = $val;
            }
            $this->returnedValues_calculation();
        }
    }

    public function returnedValues_calculation() {

        foreach ($this->returnedValues as $k => $out) {
            $out['target'] = "\$F{" . $k . "}";
            //var_dump($out);
            $subreportVariable = (string) $out['@attributes']['subreportVariable'];
            $toVariable = (string) $out['@attributes']['toVariable'];
            $row = array();
            $row[$k] = $out['ans'];
            $this->variable_calculation($k, $out, (object) $row);
        }
    }

    public function get_expression($text, $row, $writeHTML = null, $element = null) {
        preg_match_all("/P{(\w+)}/", $text, $matchesP);
        if ($matchesP) {
            foreach ($matchesP[1] as $macthP) {
                $text = str_ireplace(array('$P{' . $macthP . '}', '"'), array($this->arrayParameter[$macthP], ''), $text);
            }
        }

        preg_match_all("/V{(\w+)}/", $text, $matchesV);
        if ($matchesV) {
            foreach ($matchesV[1] as $macthV) {
                $text = $this->getValOfVariable($macthV, $text, $writeHTML, $element);
            }
        }
        
        preg_match_all("/F{[^}]*}/", $text, $matchesF);
        if ($matchesF) {
            //var_dump($matchesF);
            foreach ($matchesF[0] as $macthF) {
                $macth = str_ireplace(array("F{", "}"), "", $macthF);
                $text = $this->getValOfField($macth, $row, $text, $writeHTML);
            }
        }

        return $text;
    }

    public function getValOfVariable($variable, $text, $htmlentities = false, $element = null) {
        $val = array_key_exists($variable, $this->arrayVariable) ? $this->arrayVariable[$variable] : array();
        $ans = array_key_exists('ans', $val) ? $val['ans'] : '';
        if (preg_match_all("/V{" . $variable . "}\.toString/", $text, $matchesV) > 0) {
            //$ans = $ans+0;
            $ans = ($ans) ? number_format($ans, 2, ',', '.') : $ans;
            return str_ireplace(array('$V{' . $variable . '}.toString()'), array($ans), $text);
        } elseif (preg_match_all("/V{" . $variable . "}\.numberToText/", $text, $matchesV) > 0) {
            return str_ireplace(array('$V{' . $variable . '}.numberToText()'), array($this->numberToText($ans, false)), $text);
        } elseif (preg_match_all("/V{" . $variable . "}\.(\w+)/", $text, $matchesV) > 0) {
            $funcName = $matchesV[1][0];
            if (method_exists($this, $funcName)) {
                return str_ireplace(array('$V{' . $variable . '}'), array(call_user_func_array(array($this, $funcName), array($ans, true))), $text);
            } else {
                return str_ireplace(array('$V{' . $variable . '}'), array(call_user_func($funcName, $ans)), $text);
            }
        } elseif ($variable == "MASTER_TOTAL_PAGES") {
            return str_ireplace(array('$V{MASTER_TOTAL_PAGES}'), array('{:ptp:}'), $text);
        } elseif ($variable == "PAGE_NUMBER" || $variable == "MASTER_CURRENT_PAGE" || $variable == "CURRENT_PAGE_NUMBER" ) {
            if ( (JasperPHP\Instructions::$processingPageFooter && JasperPHP\Instructions::$lastPageFooter)
               || (isset($element->evaluationTime) && $element->evaluationTime == "Report") ) {
                return str_ireplace(array('$V{' . $variable . '}'), array('{:ptp:}'), $text);
            }
            return str_ireplace(array('$V{' . $variable . '}'), array(JasperPHP\Instructions::$currrentPage), $text);
        } else {
            return str_ireplace(array('$V{' . $variable . '}'), array($ans), $text);
        }
    }

    public function getValOfField($field, $row, $text, $htmlentities = false) {
        error_reporting(0);
        $fieldParts = strpos($field, "->") ? explode("->", $field) : explode("-&gt;", $field);
        $obj = $row;
        //var_dump($fieldParts);
        // exit;
        foreach ($fieldParts as $part) {
            if (preg_match_all("/\w+/", $part, $matArray)) {
                if (count($matArray[0]) > 1) {
                    $objArrayName = $matArray[0][0];
                    $objCounter = $matArray[0][1];
                    $obj = $obj->$objArrayName;
                    $obj = $obj[$objCounter];
                } else if (is_array($obj)) {
                    if (array_key_exists($part, $obj)) {
                        $obj = $obj[$part];
                    } else {
                        $obj = "";
                    }
                } else if (is_object($obj)) {
                    preg_match_all("/(\w+)\(\)/", $part, $matchMethod);
                    if ($matchMethod && array_key_exists(0, $matchMethod[1])) {
                        $method = $matchMethod[1][0];
                        $obj = $obj->$method();
                    } else {
                        $obj = $obj->$part;
                    }
                } else {
                    $obj = "";
                }
                
            }
        }

        $val = $obj;
        error_reporting(5);
        $fieldRegExp = str_ireplace("[", "\[", $field);
        if (preg_match_all("/F{" . $fieldRegExp . "}\.toString/", $text, $matchesV) > 0) {
            //$val = ($val)?$val:0;
            $val = ($val) ? number_format($val, 2, ',', '.') : $val;
            return str_ireplace(array('$F{' . $field . '}.toString()'), array($val), $text);
        } elseif (preg_match_all("/F{" . $fieldRegExp . "}\.numberToText/", $text, $matchesV) > 0) {
            return str_ireplace(array('$F{' . $field . '}.numberToText()'), array($this->numberToText($val, false)), $text);
        } elseif (preg_match_all("/F{" . $fieldRegExp . "}\.(\w+)\((\w+)\)/", $text, $matchesV) > 0) {
            $funcName = $matchesV[1][0];
            //return str_ireplace(array('$'.$matchesV[0][0]),array(call_user_func_array(array($this,$funcName),array($val,$matchesV[2][0]))),$text);
            if (method_exists($this, $funcName)) {
                return str_ireplace(array('$' . $matchesV[0][0]), array(call_user_func_array(array($this, $funcName), array($val, $matchesV[2][0]))), $text);
            } else {
                return str_ireplace(array('$' . $matchesV[0][0]), array(call_user_func($funcName, $val)), $text);
            }
        } elseif (preg_match_all("/F{" . $fieldRegExp . "}\.(\w+)/", $text, $matchesV) > 0) {
            $funcName = $matchesV[1][0];
            if (method_exists($this, $funcName)) {
                return str_ireplace(array('$' . $matchesV[0][0] . "()"), array(call_user_func_array(array($this, $funcName), array($val, true))), $text);
            } else {
                return str_ireplace(array('$' . $matchesV[0][0] . "()"), array(call_user_func($funcName, $val)), $text);
            }
        } elseif (is_array($val)) {
            return $val;
        } elseif ($val === false) {
            return str_ireplace('$F{' . $field . '}', '0', $text);
        } else {
            return str_ireplace(array('$F{' . $field . '}'), array(($val)), $text);
        }
    }

    public function variable_calculation($k, $out, $row) {
        preg_match_all("/P{(\w+)}/", $out['target'], $matchesP);
        if ($matchesP) {
            foreach ($matchesP[1] as $macthP) {
                $out['target'] = str_ireplace(array('$P{' . $macthP . '}'), array($this->arrayParameter[$macthP]), $out['target']);
            }
        }
        preg_match_all("/V{(\w+)}/", $out['target'], $matchesV);
        if ($matchesV) {
            foreach ($matchesV[1] as $macthV) {
                if(is_array($this->arrayVariable[$macthV])){
                    $ans = array_key_exists('ans', $this->arrayVariable[$macthV]) ? $this->arrayVariable[$macthV]['ans'] : '';
                }else{
                    $ans = '';
                }
                $defVal = $ans != '' ? $ans : $this->arrayVariable[$macthV]['initialValue'];
                $out['target'] = str_ireplace(array('$V{' . $macthV . '}'), array($ans), $out['target']);
            }
        }
        preg_match_all("/F{(\w+)}/", $out['target'], $matchesF);
        if ($matchesF) {
            foreach ($matchesF[1] as $macthF) {
                $out['target'] = $this->getValOfField($macthF, $row, $out['target']); //str_ireplace(array('$F{'.$macthF.'}'),array(utf8_encode($row->$macthF)),$out['target']); 
            }
        }
        $htmlData = array_key_exists('htmlData', $this->arrayVariable) ? $this->arrayVariable['htmlData']['class'] : '';
        if (preg_match('/(\d+)(?:\s*)([\+\-\*\/])(?:\s*)/', $out['target'], $matchesMath) > 0 && $htmlData != 'HTMLDATA') {
            
            error_reporting(0);
            $mathValue = eval('return (' . $out['target'] . ');');
            error_reporting(5);
        }
        
        $value = (array_key_exists('ans', $this->arrayVariable[$k])) ? $this->arrayVariable[$k]["ans"] : null;
        $newValue = (isset($mathValue)) ? $mathValue : $out['target'];
        $resetType = (array_key_exists('resetType', $out)) ? $out['resetType'] : '';
        
        switch ($out["calculation"]) {
            case "Sum":
                if (isset($this->arrayVariable[$k]['class']) && $this->arrayVariable[$k]['class'] == "java.sql.Time") {
                    $value = $this->time_to_sec($value);

                    $value += $this->time_to_sec($newValue);
                    $value = $this->sec_to_time($value);
                } else {
                    $value += is_numeric($newValue) ? $newValue : 0;
                }
                break;
            case "Average":
                if (isset($this->arrayVariable[$k]['class']) && $this->arrayVariable[$k]['class'] == "java.sql.Time") {
                    $value = $this->time_to_sec($value);
                    $value += $this->time_to_sec($newValue);
                    $value = $this->sec_to_time($value);
                } else {
                    $value = ($value * ($this->report_count - 1) + $newValue) / $this->report_count;
                }
                break;
            case "DistinctCount":
                break;
            case "Lowest":

                foreach ($this->dbData as $rowData) {
                    $lowest = $rowData->$out["target"];
                    if ($rowData->$out["target"] < $lowest) {
                        $lowest = $rowData->$out["target"];
                    }
                    $value = $lowest;
                }
                break;
            case "Highest":
                $out["ans"] = 0;
                foreach ($this->arraysqltable as $table) {
                    if ($rowData->$out["target"] > $out["ans"]) {
                        $value = $rowData->$out["target"];
                    }
                }
                break;
            case "Count":
                $value = $this->arrayVariable[$k]["ans"];
                $value++;
                break;
            case "":
                $value = $newValue;
                break;
        }
        if ($resetType == 'Page') {
            if ($this->pageChanged == 'true') {
                $value = $newValue;
            }
        }
        $this->arrayVariable[$k]["lastValue"] = $newValue;
        if ($resetType == 'Group') {
            if ($this->arrayGroup[$out['resetGroup']]->resetVariables == 'true') {
                $value = $newValue;
            }
        }
        
        $this->arrayVariable[$k]["ans"] = $value;
    }

    public function getPageNo() {
        $pdf = JasperPHP\Instructions::get();
        return $pdf->getPage();
    }

    public function getAliasNbPages() {
        $pdf = JasperPHP\Instructions::get();
        return $pdf->getNumPages();
    }

    public function updatePageNo($s) {
        $pdf = JasperPHP\Instructions::get();
        return str_replace('$this->PageNo()', $pdf->PageNo(), $s);
    }

    function right($value, $count) {

        return mb_substr($value, ($count * -1));
    }

    function left($string, $count) {
        return mb_substr($string, 0, $count);
    }

    public static function formatText($txt, $pattern) {
        if ($txt != '') {
            $nome_meses = array('Janeiro', 'Janeiro', 'Fevereiro', 'Marco', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
            if (substr($pattern, 0, 1) === "%")
                return sprintf($pattern,$txt);
            elseif ($pattern == "###0")
                return number_format($txt, 0, "", "");
            elseif ($pattern == "#.##0")
                return number_format($txt, 0, self::$dec_point, self::$thousands_sep);
            elseif ($pattern == "###0.0")
                return number_format($txt, 1, self::$dec_point, "");
            elseif ($pattern == "#,##0.0" || $pattern == "#,##0.0;-#,##0.0")
                return number_format($txt, 1, self::$dec_point, self::$thousands_sep);
            elseif ($pattern == "###0.00" || $pattern == "###0.00;-###0.00")
                return number_format($txt, 2, self::$dec_point, "");
            elseif ($pattern == "#,##0.00" || $pattern == "#,##0.00;-#,##0.00")
                return number_format($txt, 2, self::$dec_point, self::$thousands_sep);
            elseif ($pattern == "###0.00;(###0.00)")
                return ($txt < 0 ? "(" . number_format(abs($txt), 2, self::$dec_point, "") . ")" : number_format($txt, 2, self::$dec_point, ""));
            elseif ($pattern == "#,##0.00;(#,##0.00)")
                return ($txt < 0 ? "(" . number_format(abs($txt), 2, self::$dec_point, self::$thousands_sep) . ")" : number_format($txt, 2, self::$dec_point, self::$thousands_sep));
            elseif ($pattern == "#,##0.00;(-#,##0.00)")
                return ($txt < 0 ? "(" . number_format($txt, 2, self::$dec_point, self::$thousands_sep) . ")" : number_format($txt, 2, self::$dec_point, self::$thousands_sep));
            elseif ($pattern == "###0.000")
                return number_format($txt, 3, self::$dec_point, "");
            elseif ($pattern == "#,##0.000")
                return number_format($txt, 3, self::$dec_point, self::$thousands_sep);
            elseif ($pattern == "#,##0.0000")
                return number_format($txt, 4, self::$dec_point, self::$thousands_sep);
            elseif ($pattern == "###0.0000")
                return number_format($txt, 4, self::$dec_point, "");

            // latin formats
            elseif ($pattern == "#,##0")
                return number_format($txt, 0, ".", ",");
            elseif ($pattern == "###0,0")
                return number_format($txt, 1, ",", "");
            elseif ($pattern == "#.##0,0" || $pattern == "#.##0,0;-#.##0,0")
                return number_format($txt, 1, ",", ".");
            elseif ($pattern == "###0,00" || $pattern == "###0,00;-###0,00")
                return number_format($txt, 2, ",", "");
            elseif ($pattern == "#.##0,00" || $pattern == "#.##0,00;-#.##0,00")
                return number_format($txt, 2, ",", ".");
            elseif ($pattern == "###0,00;(###0,00)")
                return ($txt < 0 ? "(" . number_format(abs($txt), 2, ",", "") . ")" : number_format($txt, 2, ",", ""));
            elseif ($pattern == "#.##0,00;(#.##0,00)")
                return ($txt < 0 ? "(" . number_format(abs($txt), 2, ",", ".") . ")" : number_format($txt, 2, ",", "."));
            elseif ($pattern == "#.##0,00;(-#.##0,00)")
                return ($txt < 0 ? "(" . number_format($txt, 2, ",", ".") . ")" : number_format($txt, 2, ",", "."));
            elseif ($pattern == "###0,000")
                return number_format($txt, 3, ",", "");
            elseif ($pattern == "#.##0,000")
                return number_format($txt, 3, ",", ".");
            elseif ($pattern == "#.##0,0000")
                return number_format($txt, 4, ",", ".");
            elseif ($pattern == "###0,0000")
                return number_format($txt, 4, ",", "");

            elseif ($pattern == "xx/xx" && $txt != "")
                return mb_substr($txt, 0, 2) . "/" . mb_substr($txt, 2, 2);

            elseif ($pattern == "xx.xx" && $txt != "")
                return mb_substr($txt, 0, 2) . "." . mb_substr($txt, 2, 2);

            elseif (($pattern == "dd/MM/yyyy" || $pattern == "ddMMyyyy") && $txt != "")
                return date("d/m/Y", strtotime($txt));
            elseif ($pattern == "MM/dd/yyyy" && $txt != "")
                return date("m/d/Y", strtotime($txt));
            elseif ($pattern == "dd/MM/yy" && $txt != "")
                return date("d/m/y", strtotime($txt));
            elseif ($pattern == "yyyy/MM/dd" && $txt != "")
                return date("Y/m/d", strtotime($txt));
            elseif ($pattern == "dd-MMM-yy" && $txt != "")
                return date("d-M-Y", strtotime($txt));
            elseif ($pattern == "dd-MMM-yy" && $txt != "")
                return date("d-M-Y", strtotime($txt));
            elseif ($pattern == "dd/MM/yyyy h.mm a" && $txt != "")
                return date("d/m/Y h:i a", strtotime($txt));
            elseif ($pattern == "dd/MM/yyyy HH.mm.ss" && $txt != "")
                return date("d-m-Y H:i:s", strtotime($txt));
            elseif (($pattern == "dd/MM/yyyy HH:mm" || $pattern == "dd/MM/yyyy HH.mm" || $pattern == "dd/MM/yyyy H:m") && $txt != "")
                return date("d/m/Y H:i", strtotime($txt));
            elseif ($pattern == "H:m:s" && $txt != "")
                return date("H:i:s", strtotime($txt));
            elseif (($pattern == "H:m" || $pattern == "HH:mm" || $pattern == "H.m" || $pattern == "HH.mm") && $txt != "")
                return date("H:i", strtotime($txt));
            elseif (($pattern == "dFyyyy") && $txt != "")
                return date("d ", strtotime($txt)) . " de " . $nome_meses[date("n", strtotime($txt))] . " de " . date("Y", strtotime($txt));
            elseif (($pattern == "dFbyyyy") && $txt != "")
                return date("d", strtotime($txt)) . "/" . $nome_meses[date("n", strtotime($txt))] . "/" . date("Y", strtotime($txt));
            elseif (($pattern == "dFByyyy") && $txt != "")
                return date("d", strtotime($txt)) . "/" . mb_strtoupper($nome_meses[date("n", strtotime($txt))]) . "/" . date("Y", strtotime($txt));
            elseif ($pattern != "" && $txt != "") {
                return date($pattern, strtotime($txt));
            } else
                return $txt;
        } else {
            return $txt;
        }
    }

    function numberToText($valor = 0, $maiusculas = false, $money = true) {

        $singular = array(" centavo", "", " mil", "milhão", "bilhão", "trilhão", "quatrilhão");
        $plural = array(" centavos", "", " mil", "milhões", "bilhões", "trilhões",
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
        $valor = ($valor) ? $valor : 0;
        $valor = (strpos($valor, ',') == false ) ? number_format($valor, 2, '.', '.') : number_format(str_replace(',', '.', str_replace(".", "", $valor)), 2, '.', '.');
        $inteiro = explode(".", $valor);
        for ($i = 0; $i < count($inteiro); $i++)
            for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++)
                $inteiro[$i] = "0" . $inteiro[$i];

        $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
        for ($i = 0; $i < count($inteiro); $i++) {
            $valor = $inteiro[$i];
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
            $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd &&
                    $ru) ? " e " : "") . $ru;
            $t = count($inteiro) - 1 - $i;
            $r .= $r ? ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
            if ($valor == "000")
                $z++;
            elseif ($z > 0)
                $z--;
            if (($t == 1) && ($z > 0) && ($inteiro[0] > 0))
                $r .= (($z > 1) ? " de " : "") . $plural[$t];
            if ($r)
                $rt = $rt . ((($i > 0) && ($i <= $fim) &&
                        ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : "") . $r;
        }

        if (!$maiusculas) {
            return($rt ? $rt : "zero");
        } else {
            if ($rt)
                $rt = str_ireplace(" E ", " e ", ucwords($rt));
            return (($rt) ? ($rt) : "Zero");
        }
    }
    
    public function generate($obj = null) {
        //$this->parameter_handler($this->objElement, $param);
        //$this->variable_handler($this->objElement);
        //$this->queryString_handler($this->objElement);
        //var_dump($this->objElement);
        if (strlen(trim($this->sql)) > 0) {
            $this->dbData = $this->getDbData();
        }
        // exibe a tag
        $instructions = JasperPHP\Instructions::setJasperObj($obj?$obj:$this);
        parent::generate($this);
        //JasperPHP\Instructions::runInstructions();
        //JasperPHP\Instructions::clearInstructrions();
        return $this->arrayVariable;
    }

    public function out() {

        JasperPHP\Instructions::runInstructions();
        //$this->runInstructions($instructions);
    }
    public function addStyle($style){
        //print_r($style);return;
        $attributes = $style->attributes();
        $key = $attributes['name'];            
        $this->arrayStyles["{$key}"] = $style; // here you can trate all parameter of style
    }
    
    public function getStyle($key){
        if(isset($this->arrayStyles["{$key}"])){
        return $this->arrayStyles["{$key}"];
        }
    }
    public function applyStyle($key, &$reportElement, $rowData){
        $style = $this->getStyle($key);
        if($style){
            //default
            $attributes = $style->attributes();
            if(isset($style->conditionalStyle)){ 
                //percore os styles
                foreach($style->conditionalStyle as $styleNew){                
                    $expression = $styleNew->conditionExpression;             
                    //echo $expression;
                    $resultExpression = false;
                    $expression = $this->get_expression($expression, $rowData);
                    //echo 'if(' . $expression . '){$resultExpression=true;}<br/>';
                    eval('if(' . $expression . '){$resultExpression=true;}'); 
                    //echo $resultExpression."<br/>";
                    if($resultExpression){
                        //get definition style condicional
                        $attributCondicional= $styleNew->style->attributes();
                        $attributes = $attributCondicional;
                        break;
                        //var_dump($attributCondicional);  
                    }      
                }
            }           
           //change properties  
            foreach($attributes as $key => $value){
                //ignore
                if(!in_array($key,array('name'))){
                    //echo "{$key} - {$value}<br/>";    
                    $reportElement[$key]=$value;                             
                }   
            }
           
        }        
    }

}
