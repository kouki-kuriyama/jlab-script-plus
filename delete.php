<?php

//HTMLで出力する
ini_set("display_errors",0);
header("Content-type:text/html");

//クラスの読み込み
require_once("./static-data/Encryption.php");
require_once("./masterkey.php");

//設定ファイルの読み込みをする
if( file_exists("./static-data/setting.dat") ){
	$SettingsData = file_get_contents("./static-data/setting.dat");
	$SettingData = explode("\n",$SettingsData);
	
	$JlabTitle = $SettingData[0];
	$SaveFolder = $SettingData[2];
	$ThumbSaveFolder = $SettingData[3];
	$LogFolder = $SettingData[4];
	$FullURL = $SettingData[5];

}else{
	$SettingData = false;
	echo "<div style=\"margin:30px; color:red; font-size:24px\">設定ファイルがありません！</div>\n\n";
	exit;
}

//削除するファイル名を読み込む
$FileName = $_GET["Arc"];
if( !file_exists("./{$SaveFolder}/{$FileName}") ){
	header("Location: {$FullURL}");
	exit;
}

//削除モードか確認する
$DeleteKeyPure = (String)$_POST["DeleteKey"];
if( $DeleteKeyPure != "" ){
	$DeleteMode = true;
}
switch( $DeleteMode ){

	case true:
	
	//リファラーとCookieチェック
	if( !preg_match("~^{$SettingData[5]}~",$_SERVER["HTTP_REFERER"] )){
		$ResultMessage .= "パラメーターエラー";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
		break;
	}
	
	//拡張子とファイル名を分割
	list($RFileName,$ExtensionID) = explode(".",$FileName);
	
	//アップロード日を取得する
	$UploadedDate = substr($RFileName,0,6);
	
	//DATファイルを取得し、設定されている削除キーを取得する
	$ImageDatPath = "./{$LogFolder}/{$RFileName}.dat";
	$ImageDat = file_get_contents($ImageDatPath);
	$ImageDatas = explode("\n",$ImageDat);
	$SetDeleteKeyE = $ImageDatas[3];

	//削除キーを復元する
	if( $SetDeleteKeyE != "None" ){
		EncInit($MasterKey);
		$DeleteKey = DecGo($SetDeleteKeyE);
	}else{
		$ResultMessage .= "削除キーが設定されていない為、削除できません";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
		break;
	}
	
	if( preg_match("~^{$DeleteKey}$~","$DeleteKeyPure" )){
	
		unlink("./{$SaveFolder}/{$FileName}");
		unlink("./{$ThumbSaveFolder}/{$FileName}");
		unlink("./{$LogFolder}/{$RFileName}.dat");
	
		//一覧から削除する
		$ImageList = file_get_contents("./{$LogFolder}/ImageList-{$UploadedDate}.txt");
		$ImageList = explode("\n",$ImageList);
		foreach($ImageList as $key => $value) {
		  if( preg_match("~{$FileName}~",$value) ) {
		    break;
		  }
		}
		unset($ImageList[$key]);
		file_put_contents("./{$LogFolder}/ImageList-{$UploadedDate}.txt",implode("\n",$ImageList));
		
		$ResultMessage .= "{$FileName} は削除されました\n";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"button\" class=\"BlueButton\" value=\"完了\" onclick=\"location.href='./'\"></div>\n";
		break;
		
	}else{
	
		$ResultMessage .= "削除キーが一致しません\n";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
		break;
	
	}
	
	break;
	default:
	$ResultMessage .= "<form method=\"post\" action=\"delete.php?Arc={$FileName}\" name=\"DeletePanel\">\n";
	$ResultMessage .= "画像を削除します。<br>\n";
	$ResultMessage .= "アップロード時に設定した削除キーを入力してください。\n";
	$ResultMessage .= "<div style=\"margin-top:1em\"><img src=\"./{$ThumbSaveFolder}/{$FileName}\"></div>\n";
	$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"password\" id=\"DeleteKeyBox\" style=\"width:300px\" name=\"DeleteKey\" class=\"TextBox\"></div>\n";
	$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"submit\" class=\"RedButton\" value=\"削除\"> <input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
	$ResultMessage .= "</form>\n";
	break;

}
?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title><?php echo "画像を削除する : {$JlabTitle}"; ?></title>

<!-- StyleSheet -->
<style type="text/css">

/* --- Main --- */
body {
	width:100%;
	margin:0;
	padding:0;
	padding-bottom:225px !important;
	background:#f5f5f5;
	color:#000;
	font-family:Helvetica,"Meiryo UI",sans-serif;
	font-size:14px;
	text-align:left;
}

img { border:0px; }

a { color:#444; }
a:hover { text-decoration:none; }

input { margin:0 }

h1 {
	margin:0;
	padding:1em 2em;
	color:#444;
	font-size:24px;
}

#ResultP {
	padding:2em 0 2em 3em;
	background:#fff;
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
	text-align:center;
}

/* --- Input --- */
.TextBox {
	height:24px;
	padding:3px;
	background:#ffffff;
	border:2px solid #9c9c9c;
	border-radius:0px;
	outline:none;
	transition:0.5s ease;
	-webkit-transition:0.5s ease;
	-moz-transition:0.5s ease;
}

.TextBox:hover { box-shadow:0 0 7px #9c9c9c; }

.BlueButton,.RedButton {
	width:150px;
	height:30px;
	outline:none;
	border:0px;
	border-radius:0px;
	color:#fff;
	text-shadow:0 0 5px #fff;
	transition:0.5s ease;
	-webkit-transition:0.5s ease;
	-moz-transition:0.5s ease;
}
.BlueButton { background:#004ab2; }
.BlueButton:hover { box-shadow:0 0 7px #004ab2; }
.BlueButton:active { box-shadow:0 0 0 #004ab2; }

.RedButton { background:#ff4f4f; }
.RedButton:hover { box-shadow:0 0 7px #ff4f4f; }
.RedButton:active { box-shadow:0 0 0 #ff4f4f; }

</style>

</head>
<body>

<!-- Header -->
<header>
<h1><?php echo $JlabTitle; ?></h1>
</header>

<!-- Contents -->
<div id="Contents">

<!-- Result -->
<div id="ResultP">
<?php echo $ResultMessage; ?>
</div>

<!-- Footer -->
<footer>
<div style="margin:2em 3em; font-size:12px;">
	<p><a href="https://github.com/kouki-kuriyama/jlab-script-plus/" target="_blank">jlab-script-plus Ver0.02a</a></p>
</div>
</footer>

</body>
</html>	