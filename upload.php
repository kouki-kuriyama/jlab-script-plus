<?php

/*
	
	jlab-script-plus upload.php
	Version 0.06 dev4 fixed / Kouki Kuriyama
	https://github.com/kouki-kuriyama/jlab-script-plus
	
*/

//HTMLで出力する・セッションCookieのスタート
header("Content-Type:text/html; charset=UTF-8");
ini_set("display_errors",0);
session_start();

//設定ファイル・クラス・関数を読み込む
require_once("./settings.php");
require_once("./functions.php");
require_once("./static/Thumb.php");

//変数初期化
$ErrorCode = 0;
$NextUploader = "display:none";

//各種Cookieを読み込む
$LocalDeleteKey =  $_COOKIE["DeleteKey"];
$UploadTask = $_COOKIE["UploadTask"];
$Result = $_COOKIE["Result"];

//ドラッグアンドドロップアップロードかダイアログアップロードかで処理を分ける
//空欄の場合はドラッグアンドドロップアップロードの結果表示
$ExecuteType = $_POST["Type"];

//リファラー・不正リロードチェック
if(( !preg_match("~^{$FullURL}~",$_SERVER["HTTP_REFERER"] ))||( $UploadTask != "Ready" )){
	header("Location:./");
	exit;
}

//アップロードが他プロセスと重複していないか確認
//ロックが掛けられない場合は、ロックが掛けられるまで待機
$ProcessLock = fopen("./static/process.dat","a");
flock($ProcessLock,LOCK_EX);

//実行タイプがアップロードの場合はアップロードタスクを行う
if(( $ExecuteType == "dragdrop" )||( $ExecuteType == "dialog" )){

	//アップロードタイプの異なる部分だけタスクを分ける
	switch( $ExecuteType ){
		
		//ドラッグドロップアップロードの場合
		case "dragdrop":
		
			//画像を取得する
			list($Trash,$RawImage) = explode(",", $_POST["Image"]);
			if( $RawImage == "" ){
				header("Location:./");
				exit;
			}
			
			//画像をバイナリに戻してファイルサイズと情報を取得する
			$UploadFileBin = base64_decode(str_replace(' ', '+', $RawImage));
			$ImageSize = strlen($UploadFileBin);
			$ImageInfo = getimagesizefromstring($UploadFileBin);
			
			break;
			
			
		//ダイアログアップロードの場合
		case "dialog":
		
			//画像を取得する
			$RawImage = is_uploaded_file($_FILES['Image']['tmp_name']);
			if( !$RawImage ){
				header("Location:./");
				exit;
			}
			
			//画像のファイルサイズと情報を取得する
			$ImageSize = $_FILES['Image']['size'];
			$ImageInfo = getimagesize($_FILES['Image']['tmp_name']);
			
			break;
			
			
	}
		
	//画像サイズがアップロード可能サイズより大きい場合は弾く
	if( $MaxSize < $ImageSize ){
		$ErrorCode = 100;
	}
	
	//取得した画像の情報を専用の変数に代入
	$ImageWidth = $ImageInfo[0];
	$ImageHeight = $ImageInfo[1];
	$MIMETypeID = $ImageInfo[2];
	$MIMEType = $ImageInfo["mime"];
	
	//画像形式が GIF / JPEG /PNG 以外の場合は弾く
	if(( $MIMETypeID != 1 )&&( $MIMETypeID != 2 )&&( $MIMETypeID != 3 )){
		$ErrorCode = 200;
	}
	
	//エラーが無い場合はアップロードタスクを続ける
	if( $ErrorCode == 0 ){
	
		//MIMETypeIDから拡張子を設定
		if( $MIMETypeID == 1 ){
			$ExtensionID = "gif";
		}else if( $MIMETypeID == 2 ){
			$ExtensionID = "jpg";
		}else if( $MIMETypeID == 3 ){
			$ExtensionID = "png";
		}
		
		//アップロード日・時間を取得する
		$FileName = $FileBaseName.date("ymdHis");
		$UploadDate = date("ymd");
		$UploadTime = date("y/m/d H:i:s");
		$ImageFileName = "{$FileName}.{$ExtensionID}";
		
		//画像を保存する
		$ImagePath = "./{$SaveFolder}/{$ImageFileName}";
		if( $ExecuteType == "dragdrop" ){
			file_put_contents($ImagePath,$UploadFileBin);
		}else if( $ExecuteType == "dialog" ){
			move_uploaded_file($_FILES['Image']['tmp_name'],$ImagePath);
		}
		
		//削除キーを取得する
		$getDeleteKey = $_POST["DeleteKey"];
		if( $getDeleteKey != "" ){
			$DeleteKey = base64_encode(crypt($getDeleteKey,'$6$'.sha1(uniqid(mt_rand(),true))));
		}else{
			$DeleteKey = "None";
		}
		
		//サムネイル画像の作成
		$CreateThumb = new Image($ImagePath);
		$CreateThumb -> name("../{$ThumbSaveFolder}/{$FileName}");
		$CreateThumb -> width($MaxThumbWidth);
		$CreateThumb -> save();
		$ImageThumbPath = "./{$ThumbSaveFolder}/{$ImageFileName}";
		
		//パーミッション設定により画像が正しく表示されない場合は chmod関数 のコメントアウトを外して適切なパーミッションに設定してください
		//chmod($ImagePath, 0606);
		//chmod($ImageThumbPath, 0606); 
		
		//ファイルサイズを取得
		$FileSizes = round( filesize($ImagePath)/1024 );
		
		//画像情報を保存する
		$ImageDatPath = "./{$LogFolder}/{$FileName}.dat";
		$ImageData = "JLAB Base ScriptPlus DataPackage Version2\n";
		$ImageData .= "{$FileName}.{$ExtensionID}\n";
		$ImageData .= "{$MIMEType}\n";
		$ImageData .= "{$DeleteKey}\n";
		$ImageData .= $_SERVER["REMOTE_ADDR"]."\n";
		$ImageData .= $_SERVER["REMOTE_HOST"]."\n";
		$ImageData .= $_SERVER["HTTP_USER_AGENT"];
		
		//画像情報をDATファイルに保存する
		file_put_contents($ImageDatPath,$ImageData);
		
		//画像一覧を取得して新しく追加する
		$ImageListPath = "./{$LogFolder}/ImageList.txt";
		$ImageList = file_get_contents($ImageListPath);
		$ImageList = explode("\n",$ImageList);
		$NewImageListLine = "{$ImageFileName}#{$UploadTime}#{$ImageWidth}#{$ImageHeight}#{$FileSizes}";
		if( $ImageList[0] == "" ){
			$ImageList[0] = $NewImageListLine;
		}else{
			array_unshift($ImageList,$NewImageListLine);
		}
		file_put_contents($ImageListPath,implode("\n",$ImageList));
		
		//保存期間を超えた画像がある場合は削除する
		TimeLimitDeletion();
		
		//削除キーをCookieに保存し・ロックを解除して開放する
		setcookie("DeleteKey",$getDeleteKey, time()+60*60*24*14, "/");
		if( $LocalDeleteKey == "" ){
			$LocalDeleteKey = $getDeleteKey;
		}
		fclose($ProcessLock);
		
		//ドラッグドロップアップロードの場合はアップロード結果をAjaxで返す
		//　ダイアログアップロードの場合はそのまま処理表示タスクまで続ける
		if( $ExecuteType == "dragdrop" ){
			echo $ImageFileName;
			exit;
		}else{
			$Result = $ImageFileName;
		}
	}
	
	//ドラッグドロップアップロードでエラーがある場合はエラー内容をAjaxで返す
	//　ダイアログアップロードの場合はそのまま結果表示タスクまで続ける
	else{
		if( $ExecuteType == "dragdrop" ){
			echo $ErrorCode;
			exit;
		}
	}
}

//結果表示タスク
//　ドラッグアンドドロップアップロードはResultCookieから
//　ダイアログアップロードは$ErrorCodeから処理内容を取得する
if(( $ErrorCode == 100 )||( $Result == "100" )){

	setcookie("UploadTask", "Complete");
	$ResultTitle = "画像が大きすぎます";
	$ResultMessage = "画像が大きすぎます";

}else if(( $ErrorCode == 200 )||( $Result == "200" )){

	setcookie("UploadTask", "Complete");
	$ResultTitle = "この形式のファイルはアップロードできません";
	$ResultMessage = "この形式のファイルはアップロードできません";

}else if(( $ErrorCode == 0 )&&( $Result != "" )){

	//アップロードタスク正常完了
	setcookie("UploadTask", "Complete");
	$ResultTitle = "アップロードが完了しました";
	$ResultMessage = "アップロードが完了しました\n";
	$ResultMessage .= "<div style=\"margin-top:1em\"><img src=\"./{$ThumbSaveFolder}/{$Result}\"></div>\n";
			
	//URLリングが有効な場合はURLBoxを表示する
	if( $_COOKIE["URLRing"] != "" ){
		$URLRing = $_COOKIE["URLRing"];
		$ResultMessage .= "<div style=\"margin-top:1em\"><textarea id=\"urlbox-textarea\" class=\"TextBox\" wrap=\"off\" style=\"width:350px; height:80px; resize:none;\" onclick=\"this.select(0,this.value.length)\" readonly>{$URLRing}\n{$TransportURL}{$Result}</textarea></div>\n";
	}
	$ResultMessage .= "<div style=\"margin-top:1em\"><input type=\"text\" class=\"TextBox\" style=\"width:350px\" onclick=\"this.select(0,this.value.length)\" value=\"{$TransportURL}{$Result}\" readonly></div>\n";

	//セッションCookieにリングURLを追加する
	setcookie("URLRing", "{$URLRing}\n{$TransportURL}{$Result}");

	//次のアップローダーを表示しておく
	$NextUploader = "display:block";
	
	//ドラッグアンドドロップアップロードの確認
	if( $UseDragDrop === "true" ){
		$UploaderReadyMessage = "画像をブラウザ上に<strong>ドラッグアンドドロップ</strong>するか、ファイルを選択してください";
	}else{
		$UploaderReadyMessage = "ファイルを選択してください";
	}
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title><?php echo "{$ResultTitle} : {$JlabTitle}"; ?></title>

<!-- Default CSS/Javascript -->
<link type="text/css" rel="stylesheet" href="./static/jlab-script-plus.css">
<script type="text/javascript" src="./static/jlab-script-plus.js"></script>

<!-- CSS -->
<style type="text/css">
#Preview img {
	max-width:<?php echo $MaxThumbWidth; ?>px;
	max-height:<?php echo $MaxThumbHeight; ?>px;
}

.InitImage {
	width:<?php echo $MaxThumbWidth; ?>px;
	float:left;
	margin:0px 10px 0px 0px;
	padding:0px !important;
	text-align:center;
}

.InitImage img {
	max-width:<?php echo $MaxThumbWidth; ?>px;
	max-height:<?php echo $MaxThumbHeight; ?>px;
}
</style>

<!-- Javascript -->
<script type="text/javascript">
var OpenURLBox = false;
var UseDragDrop = <?php echo $UseDragDrop; ?>;
var NextUploader = true;

//ドラッグアンドドロップチェック
window.onload = function(){
	CheckEnableFileAPI();
}
</script>

</head>
<body ondragover="onFileOver(event)" ondrop="onFileDrop(event)">

<!-- DragDropCurtain -->
<div id="DragDropCurtain">
<div style="margin:2em 3em" id="DragDropCurtainT">ドロップして画像を取り込みます</div>
</div>

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
	
	<!-- NextUpload -->
	<div id="Uploader" style="margin:2em 0; <?php echo $NextUploader; ?>">
	
		<!-- Curtain -->
		<div id="UploaderCurtain">
		<div style="margin-top:30px; font-size:18px;">アップロード中です...</div>
		</div>
	
		<span id="UploaderMessage">
		続けて画像をアップロードしますか？<br>
		<?php echo $UploaderReadyMessage; ?></span>
		<form method="post" enctype="multipart/form-data" id="UploaderPanel" name="ImageUploader" action="upload.php">
			<p id="Preview"></p>
			<div style="font-weight:bold">ファイル</div>
			<div style="width:400px !important;"><input type="file" name="Image" id="UploadMedia"><span id="LoadedFileName"></span></div>
			<div style="display:none"><input type="hidden" name="Type" value="dialog"></div>
			<br style="clear:both">
			<div style="font-weight:bold">削除キー</div>
			<div style="width:400px !important;"><input type="password" id="DeleteKeyBox" name="DeleteKey" value="<?php echo $LocalDeleteKey; ?>" class="TextBox"> (Max 16Byte)</div>
			<br style="clear:both">
			<div style="width:400px"><input type="button" class="BlueButton" value="アップロード" onclick="ImageUploading()"> <input type="button" class="RedButton" value="リセット" onclick="AllClear()"></div>
			<br style="clear:both">
		</form>
		
		<ul style="list-style:none; padding:0; margin:0">
			<li>JPG GIF PNG / MAX <span style="font-size:18px"><?php echo $DisplayMaxSize; ?></span>KB / <span style="font-size:20px"><?php echo $SaveDay; ?></span>日間保存</li>
			<li>ここから続けてアップロードを行うと、URLBoxにアップロードした画像のURLが自動で追加されます</li>
		</ul>
		
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
