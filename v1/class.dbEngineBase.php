<?php

/**
 * dbEngineBase v1.1: data base operation class
 **/

class dbEngineBase{
private $location='';
private $name='';
private $user='';
private $psw='';
private $prefix='';//prefix for tables

public $error='';

var $cnx=NULL;

function __construct($location='',$name='',$user='',$psw='',$tblPrefix=''){
$this->location=$location;
$this->name=$name;
$this->user=$user;
$this->psw=$psw;
$this->prefix=$tblPrefix;

$this->connect();
}

function setError($txt,$return=false,$throwException=true){
$this->error=$txt;
if($throwException) throw new Exception($txt);
return $return;
}

function connect(){
$this->cnx=mysql_connect($this->location,$this->user,$this->psw);
if (!$this->cnx) return $this->setError('MySQL is not available now');
if (!mysql_select_db($this->name,$this->cnx)) return $this->setError('MySQL is not available now: DB was not found');
mysql_query("SET NAMES 'utf8'");
}

function close(){mysql_close($this->cnx);}

function query($query){
$query=str_replace('#',$this->prefix,$query);
$rez=mysql_query($query) or $this->setError(mysql_error());
return $rez;
}

//returns array, consisting of arr_keys-elements only
static function arrayPrepare(&$arr_source,$arr_keys){
$rez=array();
foreach($arr_keys as $key) if(isset($arr_source[$key])) $rez[$key]=$arr_source[$key];
return $rez;
}

function getOne($query){
$queryRez=$this->query($query);
$rez=mysql_result($queryRez,0);
mysql_free_result($queryRez);
return $rez;
}

function getRow($query){
$queryRez=$this->query($query);
return mysql_fetch_assoc($queryRez);
}

//just returns array (rows) of assoc-array (cols) for each row of query-result
function queryAssoc($query){
$queryRez=$this->query($query);
$rez=array();
if(mysql_num_rows($queryRez)>0) while($row=mysql_fetch_assoc($queryRez)){$rez[]=$row;}
mysql_free_result($queryRez);
return $rez; 
}

//returns single array of $col-values
function queryCol($query,$col){
$queryRez=$this->query($query);
$rez=array();
if(mysql_num_rows($queryRez)>0) while($row=mysql_fetch_assoc($queryRez)){$rez[]=$row[$col];}
mysql_free_result($queryRez);
return $rez;
}

/*
   like queryAssoc but insert SQL_CALC_FOUND_ROWS-option and return total
   row-counts in &$totalRows-parameter.
   Usefull for pagination.
   $query must be simple without limit-parameters.
   For adding numeration-column pass col-name in $insertNumCol
*/
function queryAssocLimit($query,&$totalRows,$limitFrom=0,$limitCount=0,$insertNumCol=''){
if(!$limitCount) {$totalRows=FALSE;return $this->queryAssoc($query);}
$query=str_replace('SELECT ','SELECT SQL_CALC_FOUND_ROWS ',$query);
$query.=" LIMIT {$limitFrom},{$limitCount}";
$rez=$this->queryAssoc($query);
$mysql=mysql_query("SELECT FOUND_ROWS()");
$totalRows=mysql_result($mysql,0);
if($insertNumCol) foreach($rez as $key=>$row) {$limitFrom++;$rez[$key][$insertNumCol]=$limitFrom;}
return $rez;
}

//$valArray=array('columnName1'=>'value1','columnName2'=>'value2');
//if ($doEscape) all values will be proccessed with mysql_real_escape_string
//in $nowDateColName-collumn will be placed current date-time
//if $retLastId=1 returns last insert id
function insert($table,$valArray,$doEscape=true,$nowDateColName='',$retLastId=FALSE){
if($nowDateColName) $valArray[$nowDateColName]=date("Y-m-d H:i:s");
$query='';
foreach($valArray as $col=>$val) {
	if($query) $query.=',';
	if($doEscape) $val=mysql_real_escape_string($val);
	$query.="`{$col}`='{$val}'";
}//foreach
if(!$query) return false;
$query="INSERT INTO `".$table."` SET ".$query;
$rez=$this->query($query);
if(!$retLastId) return $rez;
return mysql_insert_id($this->cnx);	
}

//executes $query, then replaces all column-names in $pattern (like: "<div>$col1</div><div>$col2</div>"),returns merged result for all returned strings 
function htmlSelect($query,$pattern){
$rez=$this->query($query);
$rezTxt='';
if(mysql_num_rows($rez)>0){
while($row=mysql_fetch_assoc($rez)){
$rowTxt=$pattern;
foreach ($row as $colName=>$colValue) $rowTxt=str_replace('$'.$colName,$colValue,$rowTxt);
$rezTxt.=$rowTxt;}
}//if
mysql_free_result($rez);
return $rezTxt; 
}

//in $nowDateColName-collumn will be placed current date-time
function update($table,$valArray,$where,$doEscape=true,$nowDateColName=''){
if($nowDateColName) $valArray[$nowDateColName]=date("Y-m-d H:i:s");
$query='';
foreach($valArray as $col=>$val) {
	if($query) $query.=',';
	if($doEscape) $val=mysql_real_escape_string($val);
	$query.="`{$col}`='{$val}'";
}//foreach
if(!$query) return false;
$query="UPDATE `{$table}` SET {$query} WHERE {$where}";
return $this->query($query);
}

//field-increment of the table with $where-condition by the only mysql-request
function incField($table,$field,$where,$i=1){
$query="UPDATE {$table} SET {$field}={$field}+{$i} WHERE {$where}";
return $this->query($query);
}

//@added 05.12.2010 sm.ART
//$rows - array of assoc-arrays like array(0=>array('col1'=>'val1','col2'=>'val2'),1=array('col1'=>'val3','col2'=>'val4')
function replaceRows($table,$rows){
$values=array();
$colnames='';
$isFirst=true;
foreach ($rows as $row) {
$addStr='';
foreach ($row as $col=>$val) {if($addStr) $addStr.=',';$addStr.='"'.$val.'"';
		if($isFirst){if($colnames) $colnames.=',';$colnames.="`{$col}`";}}
if($isFirst){$isFirst=false;$colnames="({$colnames})";}
$values[]="({$addStr})";
}//foreach1
$query="REPLACE {$table} {$colnames} VALUES".implode(',',$values);
return $this->query($query);
}

}//END class dbEngineBase
?>