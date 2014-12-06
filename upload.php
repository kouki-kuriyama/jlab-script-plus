<?php

//HTMLで出力する
header("Content-type:text/html");
ini_set("display_errors",0);

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
	$FileBaseName = $SettingData[13];
	$TransportURL = $SettingData[15];
	
	if( $FileBaseName == "" ){
		$FileBaseName = "";
	}

}else{
	$SettingData = false;
	echo "［エラー］設定ファイルがありません。<br>\n";
	echo "　　　　　スクリプトを開始するには、アップローダーの設定をする必要があります。";
	exit;
}

//ドラッグアンドドロップかファイルダイアログを使用しているかで処理を分ける
$UploadType = $_POST["type"];

//リファラーとCookieチェック
if(( !preg_match("~^{$SettingData[5]}~",$_SERVER["HTTP_REFERER"] ))||( $_SESSION["JCK"] != "Ready" )){
	header("Location:./");
	break;
}

//ファイルを確認する
$GetFile = is_uploaded_file($_FILES["Image"]["tmp_name"]);
list($Trash,$DDUploadFile) = explode(",", $_POST["Image"]);
if(( !$GetFile )&&( $DDUploadFile == "" )){
	$UploadTask = "OFF";
}else{
	$UploadTask = "ON";
}

//ドラッグアンドドロップとファイルダイアログで処理を分ける
switch( $UploadTask ){

	case "ON":
	
		//アップロードが他プロセスと重複していないか確認
		//重複してロックが掛けられない場合は、ロックが掛かるまで待機
		$UpdManageFile = "./static-data/upd-manage.dat";
		$ProcessLocking = fopen($UpdManageFile,"a");
		flock($ProcessLocking,LOCK_EX);
		
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
		$FileName = $FileBaseName.date("ymdHis");
		$UploadDate = date("ymd");
		$UploadTime = date("y/m/d H:i:s");
		
		
		//画像のサイズを取得
		if( $UploadType == "dd" ){
			$UploadingFileBin = base64_decode(str_replace(' ', '+', $DDUploadFile));
			if( $MaxSize < strlen($UploadingFileBin) ){
				echo "e201";
				exit;
			}
		}else{
			if( $MaxSize < $_FILES['Image']['size'] ){
				$ResultTitle = "画像が大きすぎます";
				$ResultMessage .= "画像が大きすぎます";
				$_SESSION["JCK"] = "Complete";
				break;
			}
		}
	
		//画像の詳細情報を取得
		if( $UploadType == "dd" ){
			$ImageInfo = getimagesizefromstring($UploadingFileBin);
		}else{
			$ImageInfo = getimagesize($_FILES['Image']['tmp_name']);
		}
		
		$ImageWidth = $ImageInfo[0];
		$ImageHeight = $ImageInfo[1];
		$MIMETypeID = $ImageInfo[2];
		$MIMEType = $ImageInfo["mime"];
		
		//画像形式を取得
		if(( $MIMETypeID != 1 )&&( $MIMETypeID != 2 )&&( $MIMETypeID != 3 )){
			if( $UploadType == "dd" ){
				echo "e202";
				exit;
			}else{
				$ResultTitle = "この形式のファイルはアップロードできません";
				$ResultMessage = "この形式のファイルはアップロードできません";
				$_SESSION["JCK"] = "Complete";
				break;
			}
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
		$ExpirationTime = time()+($SaveDay*24*60*60);
		
		//画像を保存する
		$ImagePath = "./{$SaveFolder}/{$FileName}.{$ExtensionID}";
		if( $UploadType == "dd" ){
			file_put_contents($ImagePath,$UploadingFileBin);
		}else{
			move_uploaded_file($_FILES['Image']['tmp_name'],$ImagePath);
		}
		
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
		$ImageListPath = "./{$LogFolder}/ImageList.txt";
		
		//画像一覧に追加する
		$ImageList = file_get_contents($ImageListPath);
		$ImageList = explode("\n",$ImageList);
		$NewImageListLine = "{$FileName}.{$ExtensionID}#{$UploadTime}#{$ImageWidth}#{$ImageHeight}#{$FileSizes}";
		if( $ImageList[0] == "" ){
			$ImageList[0] = $NewImageListLine;
		}else{
			array_unshift($ImageList,$NewImageListLine);
		}
		file_put_contents($ImageListPath,implode("\n",$ImageList));
		
		//もしマニュアル削除が有効な場合は保存期間を超えた画像を削除する
		if( $ManualDelete == 1 ){
		
			//日付を取得する
			$SaveDayOver = date("ymd",strtotime("- {$SaveDay} days"));
			$SaveDayOneOver = $UploadDate - 1;
			
			//既に削除済みのフラグがある場合はスキップ
			if( !file_exists("./{$LogFolder}/ManualDeleteManage-{$UploadDate}.dat")){
				
				//画像本体・ログ・サムネイルをすべて削除
				$DeleteImage = scandir("./{$SaveFolder}");
				foreach($DeleteImage as $ImageNameKey => $ImageNameValue) {

					//「.」と「..」の場合はcontinue
					if(( $ImageNameValue == "." )||( $ImageNameValue == ".." )){
						continue;
					}
					
					//拡張子と接頭語を取り外し、アップロード時間・アップロード日を取得
					$ImageNameNum = preg_replace("~[^0-9]~","",$ImageNameValue);
					$UploadedDay = substr($ImageNameNum,0,6);
					
					//もしアップロード日が保存期間を超えていたら、削除する
					if( $UploadedDay < $SaveDayOver ){
						
						//画像・サムネイル・ログファイルを削除
						unlink("./{$SaveFolder}/{$ImageNameValue}");
						unlink("./{$ThumbSaveFolder}/{$ImageNameValue}");
						unlink("./{$LogFolder}/{$FileBaseName}{$ImageNameNum}.dat");
							
						//画像一覧ログから削除する
						foreach($ImageList as $ImageListNumKey => $ImageListNumValue) {
							if( preg_match("~{$ImageNameValue}~",$ImageListNumValue) ) {
								unset($ImageList[$ImageListNumKey]);
								break;
							}
						}
					}
				
				}
				
				//画像一覧ログを再度保存する
				file_put_contents($ImageListPath,implode("\n",$ImageList));
				
				//削除済みのフラグを設定する
				if( !file_exists("./{$LogFolder}/ManualDeleteManage-{$UploadDate}.dat")){
					file_put_contents("./{$LogFolder}/ManualDeleteManage-{$UploadDate}.dat","Manual Delete Manage File.");
				}else{
					rename("./{$LogFolder}/ManualDeleteManage-{$SaveDayOneOver}.dat", "./{$LogFolder}/ManualDeleteManage-{$UploadDate}.dat");
				}
			}
		}
		
		//削除キーをCookieに保存する
		setcookie("DelKey",$DeleteKeyPure, time()+60*60*24*14, "/");
		
		//ロックを解除して開放する
		fclose($ProcessLocking);
		
		//レスポンスを返す
		if( $UploadType == "dd" ){
			echo "{$FileName}.{$ExtensionID}";
			exit;
		}else{
			$_SESSION["JCK"] = "Complete";
			$ResultTitle = "アップロードが完了しました";
			$ResultMessage = "アップロードが完了しました\n";
			$ResultMessage .= "<div style=\"margin-top:1em\"><img src=\"{$ImageThumbPath}\"></div>\n";
			$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"text\" class=\"TextBox\" style=\"width:350px\" onclick=\"this.select(0,this.value.length)\" value=\"{$TransportURL}{$FileName}.{$ExtensionID}\" readonly></div>\n";
		}
	
	break;
	
	case "OFF":
	
		//Cookieからデータを取得する
		$ImageUID = $_COOKIE["RESULT"];
	
		if( $ImageUID != "" ){
			
			//Cookieの初期化
			$_SESSION["JCK"] = "Complete";
			setcookie("RESULT", "",time() - 1800);
			
			//エラー表示
			if( $ImageUID == "e201" ){
				$ResultTitle = "画像が大きすぎます";
				$ResultMessage = "画像が大きすぎます";
			}else if( $ImageUID == "e202" ){
				$ResultTitle = "この形式のファイルはアップロードできません";
				$ResultMessage = "この形式のファイルはアップロードできません";
			}else{
				$ResultTitle = "アップロードが完了しました";
				$ResultMessage .= "アップロードが完了しました\n";
				$ResultMessage .= "<div style=\"margin-top:1em\"><img src=\"./{$ThumbSaveFolder}/{$ImageUID}\"></div>\n";
				$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"text\" class=\"TextBox\" style=\"width:350px\" onclick=\"this.select(0,this.value.length)\" value=\"{$TransportURL}{$ImageUID}\" readonly></div>\n";
			}
		
		
		}else{
			$ResultTitle = "画像がありません";
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
<title><?php echo "{$ResultTitle} : {$JlabTitle}"; ?></title>

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
	<?php echo "{$ResultMessage}\n"; ?>
	<div style="margin-top:1em"><input type="button" class="BlueButton" value="完了" onclick="location.href='./'"></div>
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
	