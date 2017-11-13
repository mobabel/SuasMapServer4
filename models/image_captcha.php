<?php
require 'AuthCode.class.php';

$vcode = base64_decode ($_GET['vcode']);
$op = $_GET['op'];

$auth_code = new AuthCode();
//return the md5 and base64 valid code
if($op == "changeCode"){
	$auth_code->setCode(array('characters'=>'0-9,a-z','length'=>4)); 
	$auth_code->generateCode();
	$vcode= $auth_code->getcode();
	echo "suc:".md5($vcode)."|".base64_encode ($vcode);

}else{
	$auth_code->setImage(array('width'=>110,'height'=>32,'type'=>'png'));
	$auth_code->setCode(array('characters'=>'0-9,a-z','length'=>4,'multicolor'=>true)); 
	$auth_code->setFont(array('file'=>"../files/fonts/en/arial.ttf", 'size'=>20));
	$auth_code->paint($vcode);
}
?>