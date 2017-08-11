<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v1.0.2
 Built on: 11/08/2017
*/

// lib/connector/index.php
class Response{public$result;public$affected;public$ind=0;public$error;public$errorData;public$outTypes;public$complete=false;public$stmt;function __construct($a,$b,&$c,&$d){$this->error=!$b;if(!$b){$this->errorData=$a->errorInfo();}else{$this->outTypes=$c;$this->init($a,$d);$this->affected=$a->rowCount();}}function init(&$a,&$d){if($d===0){$c=$this->outTypes;$e=$a->fetchAll();if($c){foreach($e as$f=>&$g){$this->map($g,$c);}}$this->result=$e;$this->complete=true;}else if($d===1){$this->stmt=$a;$this->result=array();}}function close(){$this->complete=true;if($this->stmt){$this->stmt->closeCursor();$this->stmt=null;}}function fetchNextRow(){$g=$this->stmt->fetch();if($g){if($this->outTypes){$this->map($g,$this->outTypes);}array_push($this->result,$g);return$g;}else{$this->complete=true;$this->stmt->closeCursor();$this->stmt=null;return false;}}function fetchAll(){while($g=$this->fetchNextRow()){}}function map(&$g,&$c){foreach($c as$h=>$i){if(isset($g[$h])){switch($i){case 'int':$g[$h]=(int)$g[$h];break;case 'string':$g[$h]=(string)$g[$h];break;case 'bool':$g[$h]=$g[$h]? true : false;break;case 'json':$g[$h]=json_decode($g[$h]);break;case 'obj':$g[$h]=unserialize($g[$h]);break;}}}}function error(){return$this->error ?$this->errorData : false;}function getData($j=false){if(!$this->complete&&!$j)$this->fetchAll();return$this->result;}function getAffected(){return$this->affected;}function countRows(){return count($this->result);}function next(){if(isset($this->result[$this->ind])){return$this->result[$this->ind++];}else if(!$this->complete){$g=$this->fetchNextRow();$this->ind++;return$g;}else{return false;}}function reset(){$this->ind=0;}}class Connector{public$db;public$log=array();public$dev=false;function __construct($k,$l,$m){$this->db=new \PDO($k,$l,$m);$this->log=array();}function query($n,$o=null,$c=null,$d=0){$p=$this->db->prepare($n);if($o)$q=$p->execute($o);else$q=$p->execute();if($this->dev)array_push($this->log,array($n,$o));return new Response($p,$q,$c,$d);}function _query(&$r,$s,&$t,&$c=null,$d=0){$p=$this->db->prepare($r);if($this->dev)array_push($this->log,array($r,$s,$t));foreach($s as$u=>&$v){$p->bindParam($u+1,$v[0],$v[1]);}$q=$p->execute();if(!isset($t[0])){return new Response($p,$q,$c,$d);}else{$w=array();array_push($w,new Response($p,$q,$c,0));foreach($t as$u=>$x){foreach($x as$y=>&$z){$s[$y][0]=$z;}$q=$p->execute();array_push($w,new Response($p,$q,$c,0));}return$w;}}function close(){$this->db=null;$this->queries=null;}}
// lib/parser/Simple.php
class SimParser{public static function WHERE($a,&$b,&$c){if(!empty($a)){$b.=' WHERE ';$d=0;foreach($a as$e=>$f){if($d!==0){$b.=' AND ';}$b.='`'.$e.'` = ?';array_push($c,$f);$d++;}}}public static function SELECT($g,$h,$a,$i){$b='SELECT ';$c=array();if(!isset($h[0])){$b.='*';}else{$j=count($h);for($d=0;$d<$j;$d++){if($d!==0){$b.=', ';}$b.='`'.$h[$d].'`';}}$b.=' FROM `'.$g.'`';self::WHERE($a,$b,$c);if($i)$b.=' '.$i;return array($b,$c);}public static function INSERT($g,$k){$b='INSERT INTO `'.$g.'` (';$l=') VALUES (';$c=array();$d=0;foreach($k as$e=>$f){if($d!==0){$b.=', ';$l.=', ';}$b.='`'.$e.'`';$l.='?';array_push($c,$f);$d++;}$b.=$l.')';return array($b,$c);}public static function UPDATE($g,$k,$a){$b='UPDATE `'.$g.'` SET ';$c=array();$d=0;foreach($k as$e=>$f){if($d!==0){$b.=', ';}$b.='`'.$e.'` = ?';array_push($c,$f);$d++;}self::WHERE($a,$b,$c);return array($b,$c);}public static function DELETE($g,$a){$b='DELETE FROM `'.$g.'`';$c=array();self::WHERE($a,$b,$c);return array($b,$c);}}
// lib/parser/Advanced.php
class AdvParser{static function getArg(&$a){if($a[0]==='['&&substr($a,3,1)===']'){$b=substr($a,1,2);$a=substr($a,4);return$b;}else{return false;}}static function append(&$c,$d,$e,$f){if(is_array($d)&&$f[$e][2]<5){$g=count($d);for($h=1;$h<$g;$h++){if(!isset($c[$h-1]))$c[$h-1]=array();$c[$h-1][$e]=$d[$h];}}}static function append2(&$i,$j,$k,$f){function stripArgs(&$l){if(substr($l,-1)===']'){$m=strrpos($l,'[',-1);$l=substr($l,0,$m);}$m=strrpos($l,']',-1);if($m!==false)$l=substr($l,$m+1);if($l[0]==='#'){$l=substr($l,1);}}function escape($d,$k){if(!isset($k[2]))return$d;switch($k[2]){case 0: return$d ? '1' : '0';break;case 1: return(int)$d;break;case 2: return(string)$d;break;case 3: return$d;break;case 4: return null;break;case 5: return json_encode($d);break;case 6: return serialize($d);break;}}function recurse(&$n,$d,$j,$o,$f){foreach($d as$h=>&$p){stripArgs($h);$q=$h.'#'.$o;if(isset($j[$q]))$r=$j[$q];else$r=$j[$h];$s=is_array($p)&&(!isset($f[$r][2])||$f[$r][2]<5);if($s){if(isset($p[0])){foreach($p as$t=>&$u){$v=$r+$t;if(isset($n[$v]))echo 'SUPERSQL WARN: Key collision: '.$h;$n[$v]=escape($u,$f[$v]);}}else{recurse($n,$p,$j,$o.'/'.$h,$f);}}else{if(isset($n[$r]))echo 'SUPERSQL WARN: Key collision: '.$h;$n[$r]=escape($p,$f[$r]);}}}$g=count($k);for($l=1;$l<$g;$l++){$d=$k[$l];if(!isset($i[$l-1]))$i[$l-1]=array();recurse($i[$l-1],$d,$j,'',$f);}}static function quote($a){if(strpos($a,'.')===false){return '`'.$a.'`';}else{$a=explode('.',$a);$b='';$w=count($a);for($t=0;$t<$w;$t++){if($t!==0)$b.='.';$b.='`'.$a[$t].'`';}return$b;}}static function table($x){if(is_array($x)){$y='';$w=count($x);for($t=0;$t<$w;$t++){$z=self::getType($x[$t]);if($t!==0)$y.=', ';$y.='`'.$x[$t].'`';if($z)$y.=' AS `'.$z.'`';}return$y;}else{return '`'.$x.'`';}}static function value($aa,$ba){$ca=$aa ?$aa : gettype($ba);$aa=\PDO::PARAM_STR;$da=2;if($ca==='integer'||$ca==='int'||$ca==='double'||$ca==='doub'){$aa=\PDO::PARAM_INT;$da=1;$ba=(int)$ba;}else if($ca==='string'||$ca==='str'){$ba=(string)$ba;$da=2;}else if($ca==='boolean'||$ca==='bool'){$aa=\PDO::PARAM_BOOL;$ba=$ba ? '1' : '0';$da=0;}else if($ca==='null'||$ca==='NULL'){$da=4;$aa=\PDO::PARAM_NULL;$ba=null;}else if($ca==='resource'||$ca==='lob'){$aa=\PDO::PARAM_LOB;$da=3;}else if($ca==='json'){$da=5;$ba=json_encode($ba);}else if($ca==='obj'){$da=6;$ba=serialize($ba);}else{$ba=(string)$ba;echo 'SUPERSQL WARN: Invalid type '.$ca.' Assumed STRING';}return array($ba,$aa,$da);}static function getType(&$a){$g=strlen($a);if($a[$g-1]===']'){$ea=strrpos($a,'[');if($ea===false){return '';}$b=substr($a,$ea+1,-1);$a=substr($a,0,$ea);return$b;}else return '';}static function rmComments($a){$t=strpos($a,'#');if($t!==false){$a=trim(substr($a,0,$t));}return$a;}static function conditions($k,&$f=false,&$fa=false,&$e=0){$ga=function(&$ga,$k,&$fa,&$e,&$f,$ha=' AND ',$ia=' = ',$ja=''){$ka=0;$y='';foreach($k as$l=>&$d){if($l[0]==='#'){$la=true;$l=substr($l,1);}else{$la=false;}$ma=self::getArg($l);$na=$ma ? self::getArg($l): false;$oa=!isset($d[0]);$pa=$ha;$qa=$ia;$aa=$la ? false : self::getType($l);$ra=self::quote(self::rmComments($l));switch($ma){case '||':$ma=$na;$pa=' OR ';break;case '&&':$ma=$na;$pa=' AND ';break;}switch($ma){case '!=':$qa=' != ';break;case '>>':$qa=' > ';break;case '<<':$qa=' < ';break;case '>=':$qa=' >= ';break;case '<=':$qa=' <= ';break;case '~~':$qa=' LIKE ';break;case '!~':$qa=' NOT LIKE ';break;default: if(!$oa||$ma==='==')$qa=' = ';break;}if($ka!==0)$y.=$ha;if(is_array($d)&&$aa!=='json'&&$aa!=='obj'){if($oa){$y.='('.$ga($ga,$d,$fa,$e,$f,$pa,$qa,$ja.'/'.$l).')';}else{if($fa!==false&&!$la){$fa[$l]=$e;$fa[$l.'#'.$ja]=$e++;}foreach($ba as$h=>&$p){if($h!==0)$y.=$pa;$e++;$y.=$ra.$qa;if($la){$y.=$p;}else if($f!==false){$y.='?';array_push($f,self::value($aa,$p));}else{if(is_int($p)){$y.=$p;}else{$y.=self::quote($p);}}}}}else{$y.=$ra.$qa;if($la){$y.=$d;}else{if($f!==false){$y.='?';array_push($f,self::value($aa,$d));}else{if(is_int($d)){$y.=$d;}else{$y.=self::quote($d);}}if($fa!==false){$fa[$l]=$e;$fa[$l.'#'.$ja]=$e++;}}}$ka++;}return$y;};return$ga($ga,$k,$fa,$e,$f);}static function JOIN($ha,&$y){foreach($ha as$l=>&$d){if($l[0]==='#'){$la=true;$l=substr($l,1);}else{$la=false;}$ma=self::getArg($l);switch($ma){case '<<':$y.=' RIGHT JOIN ';break;case '>>':$y.=' LEFT JOIN ';break;case '<>':$y.=' FULL JOIN ';break;default:$y.=' JOIN ';break;}$y.='`'.$l.'` ON ';if($la){$y.='val';}else{$y.=self::conditions($d);}}}static function SELECT($x,$sa,$ta,$ha,$ua){$y='SELECT ';$f=array();$i=array();$va=null;if(!isset($sa[0])){$y.='*';}else{$wa=0;$xa='';if($sa[0]==='DISTINCT'){$wa=1;$y.='DISTINCT ';array_splice($sa,0,1);}else if(substr($sa[0],0,11)==='INSERT INTO'){$wa=1;$y=$sa[0].' '.$y;array_splice($sa,0,1);}else if(substr($sa[0],0,4)==='INTO'){$wa=1;$xa=' '.$sa[0].' ';array_splice($sa,0,1);}if(isset($sa[0])){foreach($sa as$t=>&$d){$m=self::getType($d);$z=$m ? self::getType($d): false;if(!$z&&$m){if(!($m==='int'||$m==='string'||$m==='json'||$m==='obj'||$m==='bool')){$z=$m;$m=false;}}if($m){if(!$va)$va=array();if($z){$va[$z]=$m;}else{$va[$d]=$m;}}if($t!=0){$y.=', ';}$y.=self::quote($d);if($z)$y.=' AS `'.$z.'`';}}else$y.='*';$y.=$xa;}$y.=' FROM '.self::table($x);if($ha){self::JOIN($ha,$y);}if(!empty($ta)){$y.=' WHERE ';$e=array();if(isset($ta[0])){$y.=self::conditions($ta[0],$f,$e);self::append2($i,$e,$ta,$f);}else{$y.=self::conditions($ta,$f,$e);}}if($ua){if(is_int($ua)){$y.=' LIMIT '.$ua;}else if(is_string($ua)){$y.=' '.$ua;}}return array($y,$f,$i,$va);}static function INSERT($x,$ya){$y='INSERT INTO '.self::table($x).' (';$f=array();$i=array();$za='';$t=0;$m=0;$j=array();$ab=isset($ya[0]);$k=$ab ?$ya[0]:$ya;foreach($k as$l=>&$d){if($l[0]==='#'){$la=true;$l=substr($l,1);}else{$la=false;}if($m!==0){$y.=', ';$za.=', ';}$aa=self::getType($l);$y.='`'.self::rmComments($l).'`';if($la){$za.=$d;}else{$za.='?';array_push($f,self::value($aa,$d));if($ab){$j[$l]=$t++;}else{self::append($i,$d,$t++,$f);}}$m++;}if($ab)self::append2($i,$j,$ya,$f);$y.=') VALUES ('.$za.')';return array($y,$f,$i);}static function UPDATE($x,$ya,$ta){$y='UPDATE '.self::table($x).' SET ';$f=array();$i=array();$t=0;$m=0;$j=array();$ab=isset($ya[0]);$k=$ab ?$ya[0]:$ya;foreach($k as$l=>&$d){if($l[0]==='#'){$la=true;$l=substr($l,1);}else{$la=false;}if($m!==0){$y.=', ';}if($la){$y.='`'.$l.'` = '.$d;}else{$ma=self::getArg($l);$y.='`'.$l.'` = ';switch($ma){case '+=':$y.='`'.$l.'` + ?';break;case '-=':$y.='`'.$l.'` - ?';break;case '/=':$y.='`'.$l.'` / ?';break;case '*=':$y.='`'.$l.'` * ?';break;default:$y.='?';break;}$aa=self::getType($l);array_push($f,self::value($aa,$d));if($ab){$j[$l]=$t++;}else{self::append($i,$d,$t++,$f);}}$m++;}if($ab)self::append2($i,$j,$ya,$f);if(!empty($ta)){$y.=' WHERE ';$e=array();if(isset($ta[0])){$y.=self::conditions($ta[0],$f,$e,$t);self::append2($i,$e,$ta,$f);}else{$y.=self::conditions($ta,$f,$e,$t);}}return array($y,$f,$i);}static function DELETE($x,$ta){$y='DELETE FROM '.self::table($x);$f=array();$i=array();if(!empty($ta)){$y.=' WHERE ';$e=array();if(isset($ta[0])){$y.=self::conditions($ta[0],$f,$e);self::append2($i,$e,$ta,$f);}else{$y.=self::conditions($ta,$f,$e);}}return array($y,$f,$i);}}
// index.php
class SuperSQL{public$con;public$lockMode=false;function __construct($a,$b,$c){$this->con=new Connector($a,$b,$c);}function SELECT($d,$e=array(),$f=array(),$g=null,$h=false){if((is_int($g)||is_string($g))&&!$h){$h=$g;$g=null;}$i=AdvParser::SELECT($d,$e,$f,$g,$h);return$this->con->_query($i[0],$i[1],$i[2],$i[3],$this->lockMode ? 0 : 1);}function INSERT($d,$j){$i=AdvParser::INSERT($d,$j);return$this->con->_query($i[0],$i[1],$i[2]);}function UPDATE($d,$j,$f=array()){$i=AdvParser::UPDATE($d,$j,$f);return$this->con->_query($i[0],$i[1],$i[2]);}function DELETE($d,$f=array()){$i=AdvParser::DELETE($d,$f);return$this->con->_query($i[0],$i[1],$i[2]);}function sSELECT($d,$e=array(),$f=array(),$k=""){$i=SimParser::SELECT($d,$e,$f,$k);return$this->con->query($i[0],$i[1],null,$this->lockMode ? 0 : 1);}function sINSERT($d,$j){$i=SimParser::INSERT($d,$j);return$this->con->query($i[0],$i[1]);}function sUPDATE($d,$j,$f=array()){$i=SimParser::UPDATE($d,$j,$f);return$this->con->query($i[0],$i[1]);}function sDELETE($d,$f=array()){$i=SimParser::DELETE($d,$f);return$this->con->query($i[0],$i[1]);}function query($l,$m=null){return$this->con->query($l,$m);}function close(){$this->con->close();}function dev(){$this->con->dev=true;}function getLog(){return$this->con->log;}function transact($n){$this->con->db->beginTransaction();$o=$n($this);if($o===false)$this->con->db->rollBack();else$this->con->db->commit();return$o;}function modeLock($p){$this->lockMode=$p;}}
?>