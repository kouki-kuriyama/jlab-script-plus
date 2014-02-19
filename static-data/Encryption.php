<?php

function EncInit($EncKey){

	$GLOBALS["Newtd"] = mcrypt_module_open('des', '', 'ecb', '');
	$GLOBALS["EncKey"] = substr($EncKey, 0, mcrypt_enc_get_key_size($GLOBALS["Newtd"]));
	$GLOBALS["IV"]  = mcrypt_create_iv(mcrypt_enc_get_iv_size($GLOBALS["Newtd"]), MCRYPT_RAND);
	mcrypt_generic_init($GLOBALS["Newtd"],$GLOBALS["EncKey"] ,$GLOBALS["IV"]);

	return;
}

function EncGo($PText){
	$EncText = (String)base64_encode(mcrypt_generic($GLOBALS["Newtd"],$PText));
	mcrypt_generic_deinit($GLOBALS["Newtd"]);
	mcrypt_module_close($GLOBALS["Newtd"]);
	return trim($EncText);
}

function DecGo($EncdText){
	$DecText = (String)mdecrypt_generic($GLOBALS["Newtd"], base64_decode($EncdText));
 	mcrypt_generic_deinit($GLOBALS["Newtd"]);
	mcrypt_module_close($GLOBALS["Newtd"]);
	return trim($DecText);
}
?>