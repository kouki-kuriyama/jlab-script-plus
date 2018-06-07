<?php

/*

	jlab-script-plus delete.php
	Version 0.07 / Kouki Kuriyama
	https://github.com/kouki-kuriyama/jlab-script-plus

*/

//HTMLで出力する
ini_set("display_errors",0);
header("Content-type:text/html");

//設定を読み込む
require_once("./settings.php");

//削除が他プロセスと重複していないか確認
//重複してロックが掛けられない場合は、ロックが掛かるまで待機
$ProcessLock = fopen("./static/process.dat","a");
flock($ProcessLock,LOCK_EX);

//削除するファイル名を読み込む
$FileName = $_GET["Arc"];
if( !file_exists("./{$SaveFolder}/{$FileName}") ){
	header("Location: {$FullURL}");
	exit;
}

//削除モードか確認する
$getDeleteKey = (String)$_POST["DeleteKey"];
if( $getDeleteKey != "" ){
	$DeleteMode = true;
}
switch( $DeleteMode ){

	case true:

	//リファラーとCookieチェック
	if( !preg_match("~^{$FullURL}~",$_SERVER["HTTP_REFERER"] )){
		$ResultMessage .= "パラメーターエラー";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
		break;
	}

	//拡張子とファイル名を分割
	list($RFileName,$ExtensionID) = explode(".",$FileName);

	//拡張子と接頭語を取り外す
	$NDFileName_oq = preg_replace("~[^0-9]~","",$RFileName);

	//アップロード日を取得する
	$UploadedDate = substr($NDFileName_oq,0,6);

	//DATファイルを取得し、設定されている削除キーを取得する
	$ImageDatPath = "./{$LogFolder}/{$RFileName}.dat";
	$ImageDat = file_get_contents($ImageDatPath);
	$ImageDatas = explode("\n",$ImageDat);
	$ImageDeleteKey = $ImageDatas[3];

	//旧バージョンのログファイルの場合は削除不可(管理者問い合わせ)
	if( preg_match("~0\.1~",$ImageDatas[0]) ){
		$ResultMessage .= "旧バージョンの管理ファイルが使用されている為、削除できません<br>";
		$ResultMessage .= "詳しくはアップローダー管理者様でお問い合わせください";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
		break;
	}

	//削除キーを復元する
	if( $ImageDeleteKey == "None" ){
		$ResultMessage .= "削除キーが設定されていない為、削除できません";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
		break;
	}

	//削除キーが一致しているかを確認する
	if( crypt($getDeleteKey,base64_decode($ImageDeleteKey)) === base64_decode($ImageDeleteKey)) {

		unlink("./{$SaveFolder}/{$FileName}");
		unlink("./{$ThumbSaveFolder}/{$FileName}");
		unlink("./{$LogFolder}/{$RFileName}.dat");

		//画像一覧ログから削除する
		$ImageList = file_get_contents("./{$LogFolder}/ImageList.txt");
		$ImageList = explode("\n",$ImageList);
		foreach($ImageList as $key => $value) {
			if( preg_match("~{$FileName}~",$value) ) {
				break;
			}
		}
		unset($ImageList[$key]);
		file_put_contents("./{$LogFolder}/ImageList.txt",implode("\n",$ImageList));

		$ResultMessage .= "{$FileName} は削除されました\n";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"button\" class=\"BlueButton\" value=\"完了\" onclick=\"location.href='./'\"></div>\n";
		break;

	}else{
		$ResultMessage .= "<form method=\"post\" action=\"delete.php?Arc={$FileName}\" name=\"DeletePanel\">\n";
		$ResultMessage .= "<span style=\"font-weight:bold; color:red\">削除キーが一致しません</span><br>\n";
		$ResultMessage .= "画像を削除します<br>\n";
		$ResultMessage .= "アップロード時に設定した削除キーを入力してください\n";
		$ResultMessage .= "<div style=\"margin-top:1em\"><img src=\"./{$ThumbSaveFolder}/{$FileName}\"></div>\n";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"password\" id=\"DeleteKeyBox\" style=\"width:300px\" name=\"DeleteKey\" class=\"TextBox\"></div>\n";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"submit\" class=\"RedButton\" value=\"削除\"> <input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
		$ResultMessage .= "</form>\n";
		break;

	}

	break;
	default:
	$ResultMessage .= "<form method=\"post\" action=\"delete.php?Arc={$FileName}\" name=\"DeletePanel\">\n";
	$ResultMessage .= "画像を削除します<br>\n";
	$ResultMessage .= "アップロード時に設定した削除キーを入力してください\n";
	$ResultMessage .= "<div style=\"margin-top:1em\"><img src=\"./{$ThumbSaveFolder}/{$FileName}\"></div>\n";
	$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"password\" id=\"DeleteKeyBox\" style=\"width:300px\" name=\"DeleteKey\" class=\"TextBox\"></div>\n";
	$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"submit\" class=\"RedButton\" value=\"削除\"> <input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
	$ResultMessage .= "</form>\n";
	break;

}


//ロックを解除して開放する
fclose($ProcessLock);

?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title><?php echo "画像を削除する : {$JlabTitle}"; ?></title>

<!-- Default CSS/Javascript -->
<link type="text/css" rel="stylesheet" href="./static/jlab-script-plus.css">
<script type="text/javascript" src="./static/jlab-script-plus.js"></script>

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

</div>

<!-- Footer -->
<footer>
<div style="margin:2em 3em;">
	<p><a href="https://github.com/kouki-kuriyama/jlab-script-plus/" target="_blank"><script type="text/javascript">document.write(VersionNumber);</script></a></p>
</div>
</footer>

</body>
</html>
