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
	$DelKeyByPass = (int)$SettingData[12];

}else{
	$SettingData = false;
	echo "［エラー］設定ファイルがありません。<br>\n";
	echo "　　　　　スクリプトを開始するには、アップローダーの設定をする必要があります。";
	exit;
}

//削除が他プロセスと重複していないか確認
//重複してロックが掛けられない場合は、ロックが掛かるまで待機
$UpdManageFile = "./static-data/upd-manage.dat";
$ProcessLocking = fopen($UpdManageFile,"a");
flock($ProcessLocking,LOCK_EX);


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
	
	//拡張子と接頭語を取り外す
	$NDFileName_oq = preg_replace("~[^0-9]~","",$RFileName);
	
	//アップロード日を取得する
	$UploadedDate = substr($NDFileName_oq,0,6);
	
	//DATファイルを取得し、設定されている削除キーを取得する
	$ImageDatPath = "./{$LogFolder}/{$RFileName}.dat";
	$ImageDat = file_get_contents($ImageDatPath);
	$ImageDatas = explode("\n",$ImageDat);
	$SetDeleteKeyE = $ImageDatas[3];

	//削除キーを復元する
	if( $SetDeleteKeyE != "None" ){
		if( $DelKeyByPass == 1 ){
			$DeleteKey = $SetDeleteKeyE;
		}else{
			EncInit($MasterKey);
			$DeleteKey = DecGo($SetDeleteKeyE);
		}
	}else{
		$ResultMessage .= "削除キーが設定されていない為、削除できません";
		$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"button\" class=\"BlueButton\" value=\"戻る\" onclick=\"location.href='./'\"></div>\n";
		break;
	}
	
	if( preg_match("~^{$DeleteKey}$~","$DeleteKeyPure" )){
	
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
fclose($ProcessLocking);

?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title><?php echo "画像を削除する : {$JlabTitle}"; ?></title>

<!-- Default CSS/Javascript -->
<link type="text/css" rel="stylesheet" href="./static-data/jlab-script-plus.css">
<script type="text/javascript" src="./static-data/jlab-script-plus.js"></script>

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