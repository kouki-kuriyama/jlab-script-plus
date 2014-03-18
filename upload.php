<?php

//HTMLで出力する
ini_set("display_errors",0);
header("Content-type:text/html");
session_start();

//クラスの読み込み
require_once("./static-data/Thumb.php");
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
	$MaxSize = (int)$SettingData[6];
	$MaxThumbWidth = (int)$SettingData[7];
	$SaveDay = (int)$SettingData[10];
	$ManualDelete = (int)$SettingData[11];
	$DelKeyByPass = (int)$SettingData[12];

}else{
	$SettingData = false;
	echo "<div style=\"margin:30px; color:red; font-size:24px\">設定ファイルがありません！</div>\n\n";
	exit;
}

//有効なファイルか確認する
$GetFile = is_uploaded_file($_FILES['Image']['tmp_name']);
switch( $GetFile ){

	case true:
	
	//リファラーとCookieチェック
	if(( !preg_match("~^{$SettingData[5]}~",$_SERVER["HTTP_REFERER"] ))||( $_SESSION["JCK"] != "Ready" )){
		$ResultMessage = "パラメーターエラー";
		break;
	}

	//削除キーを取得する
	$DeleteKeyPure = $_POST["DeleteKey"];
	if( $DeleteKeyPure != "" ){
		if( $DelKeyByPass == 1 ){
			$DeleteKey = $DeleteKeyPure;
		}else{
			EncInit($MasterKey);
			$DeleteKey = EncGo($DeleteKeyPure);
		}
	}else{
		$DeleteKey = "None";
	}

	//現在の時間を取得する
	$FileName = date("ymdHis");
	$UploadDate = date("ymd");
	$UploadTime = date("y/m/d H:i:s");
	
	//画像のサイズを取得
	if( $MaxSize < $_FILES['Image']['size'] ){
		$ResultMessage .= "画像が大きすぎます";
		$_SESSION["JCK"] = "Complete";
		break;
	}
	
	//画像の詳細情報を取得
	$ImageInfo = getimagesize($_FILES['Image']['tmp_name']);
	$ImageWidth = $ImageInfo[0];
	$ImageHeight = $ImageInfo[1];
	$MIMETypeID = $ImageInfo[2];
	$MIMEType = $ImageInfo[mime];
	
	//画像形式を取得
	//$_FILES['Image']['type']による判定は不完全の為、廃止しました。
	// 1=gif, 2=jpeg/jpg, 3=png
	if(( $MIMETypeID != 1 )&&( $MIMETypeID != 2 )&&( $MIMETypeID != 3 )){
		$ResultMessage = "この形式のファイルはアップロードできません";
		$_SESSION["JCK"] = "Complete";
		break;
	}
	
	//拡張子を設定
	if( $MIMETypeID == 1 ){
		$ExtensionID = "gif";
	}else if( $MIMETypeID == 2 ){
		$ExtensionID = "jpg";
	}else if( $MIMETypeID == 3 ){
		$ExtensionID = "png";
	}

	//有効期限を取得する(タイムスタンプ形式)
	//※有効期限は1週間です
	$ExpirationTime = time()+(7*24*60*60);
	
	//画像を保存する
	$ImagePath = "./{$SaveFolder}/{$FileName}.{$ExtensionID}";
	move_uploaded_file($_FILES['Image']['tmp_name'],$ImagePath);
	
	//サムネイル画像の作成
	$CreateThumb = new Image($ImagePath);
	$CreateThumb -> name("../{$ThumbSaveFolder}/{$FileName}");
	$CreateThumb -> width($MaxThumbWidth);
	$CreateThumb -> save();
	$ImageThumbPath = "./{$ThumbSaveFolder}/{$FileName}.{$ExtensionID}";
	
	//ファイルサイズを取得
	$FileSizes = round( filesize($ImagePath)/1024 );
	
	//画像情報を保存する
	$ImageDatPath = "./{$LogFolder}/{$FileName}.dat";
	$ImageData = "JLAB Base ScriptPlus DataPackage Version0.1\n";
	$ImageData .= "{$FileName}.{$ExtensionID}\n";
	$ImageData .= "{$MIMEType}\n";
	$ImageData .= "{$DeleteKey}\n";
	$ImageData .= "{$ExpirationTime}\n";
	$ImageData .= $_SERVER["REMOTE_ADDR"]."\n";
	$ImageData .= $_SERVER["REMOTE_HOST"]."\n";
	$ImageData .= $_SERVER["HTTP_USER_AGENT"];
	
	//画像情報をDATファイルに保存する
	file_put_contents($ImageDatPath,$ImageData);
	
	//画像一覧を取得する
	$ImageListPath = "./{$LogFolder}/ImageList-{$UploadDate}.txt";
	
	//画像一覧に追加する（Lock対応版）
	$ImageList = file_get_contents($ImageListPath);
	$ImageList_array = explode("\n",$ImageList);
	$ImageListOpen = fopen($ImageListPath,"w");
	flock($ImageListOpen,LOCK_EX);
	
	if( $ImageList_array[0] == "" ){
		fwrite($ImageListOpen, "{$FileName}.{$ExtensionID}#{$UploadTime}#{$ImageWidth}#{$ImageHeight}#{$FileSizes}");
	}else{
		fwrite($ImageListOpen, "{$FileName}.{$ExtensionID}#{$UploadTime}#{$ImageWidth}#{$ImageHeight}#{$FileSizes}\n");
		fwrite($ImageListOpen, implode("\n",$ImageList_array));
	}
	flock($ImageListOpen, LOCK_UN);
	fclose($ImageListOpen);
	
	//削除キーをCookieに保存する
	setcookie("DelKey",$DeleteKeyPure, time()+60*60*24*14, "/");
	
	//もしマニュアル削除が有効な場合は保存期間を超えた画像を削除する
	if( $ManualDelete == 1 ){
	
		//日付を取得する
		$SnDay = $SaveDay + 1;
		$DelDate = date("ymd", strtotime("- {$SnDay} days"));
		
		//ログファイルを確認
		if( file_exists("./{$LogFolder}/ImageList-{$DelDate}.txt")){
		
			//すべてのフォルダをスキャン
			$DeleteImage = scandir("./{$SaveFolder}");
			$DeleteThumb = scandir("./{$ThumbSaveFolder}");
			$DeleteLog = scandir("./{$LogFolder}");
		
			//画像本体を削除
			foreach($DeleteImage as $IKey => $IValue) {
				if( preg_match("~^{$DelDate}~",$IValue) ){
					unlink("./{$SaveFolder}/{$DeleteImage[$IKey]}");
				}
			}
			
			//サムネイルを削除
			foreach($DeleteThumb as $TKey => $TValue) {
				if( preg_match("~^{$DelDate}~",$TValue) ){
					unlink("./{$ThumbSaveFolder}/{$DeleteThumb[$TKey]}");
				}
			}
			
			//個別ログファイルを削除
			foreach($DeleteLog as $LKey => $LValue) {
				if( preg_match("~^{$DelDate}~",$LValue) ){
					unlink("./{$LogFolder}/{$DeleteLog[$LKey]}");
				}
			}
			
			//ImageListログファイルを削除
			unlink("./{$LogFolder}/ImageList-{$DelDate}.txt");
			
		}
	}
	
	$_SESSION["JCK"] = "Complete";
	$ResultMessage .= "アップロードが完了しました\n";
	$ResultMessage .= "<div style=\"margin-top:1em\"><img src=\"{$ImageThumbPath}\"></div>\n";
	$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"text\" class=\"TextBox\" style=\"width:350px\" onclick=\"this.select(0,this.value.length)\" value=\"{$FullURL}{$SaveFolder}/{$FileName}.{$ExtensionID}\" readonly></div>\n";
	
	break;
	
	case false:
	
	//リファラーチェック
	if( !preg_match("~^{$SettingData[5]}~",$_SERVER["HTTP_REFERER"] )){
		$ResultMessage = "パラメーターエラー";
		break;
	}else{
		$ResultMessage = "画像がありません\n";
	}
	
	break;

}
?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title><?php echo "{$ResultMessage} : {$JlabTitle}"; ?></title>

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
<?php echo "{$ResultMessage}\n"; ?>
<div style="margin-top:1em"><input type="button" class="BlueButton" value="完了" onclick="location.href='./'"></div>
</div>

<!-- Footer -->
<footer>
<div style="margin:2em 3em; font-size:12px;">
	<p><a href="https://github.com/kouki-kuriyama/jlab-script-plus/" target="_blank">jlab-script-plus Ver0.03a</a></p>
</div>
</footer>

</body>
</html>
	