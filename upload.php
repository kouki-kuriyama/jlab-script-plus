<?php

/*
	jlab-script-plus upload.php
	Version 0.07b / Kouki Kuriyama
	https://www.github.com/kouki-kuriyama/jlab-script-plus
*/

//HTMLで出力する・エラーメッセージを表示しない
header("Content-Type:text/plain; charset=UTF-8");
ini_set("display_errors",0);

//設定ファイル・クラス・関数を読み込む
require_once("./functions.php");
require_once("./settings.php");
require_once("./static/Thumb.php");

//アップロードが他プロセスと重複していないか確認
//ロックが掛けられない場合は、ロックが掛けられるまで待機
$ProcessLock = fopen("./static/process.dat","a");
flock($ProcessLock,LOCK_EX);

//送信されたPOSTデータを取得する
$ExecuteType = $_POST["Type"];
$getDeleteKey = $_POST["DeleteKey"];

//ベーシックアップロードは1枚のみアップロード可能
if( $ExecuteType == "Enhance" ){
	$MaxBox = $_POST["MaxBox"];
	$Uploading = $_POST["Uploading"];
}else{
	$MaxBox = 1;
	$Uploading = 1;
}
//エンハンスアップロードとベーシックアップロードで処理を分ける
switch( $ExecuteType ){

	//エンハンスアップロード
	case "Enhance":
		
		//画像を取得する
		list($Trash,$RawImage) = explode(",",$_POST["Image"]);
		
		//画像をバイナリに戻してファイルサイズと情報を取得する
		$UploadFileBin = base64_decode(str_replace(' ', '+', $RawImage));
		$ImageSize = strlen($UploadFileBin);
		$ImageInfo = getimagesizefromstring($UploadFileBin);
		
	break;
	
	//ベーシックアップロード
	case "Basic":
		
		//画像を取得する
		$UploadFileBin = is_uploaded_file($_FILES["Image"]["tmp_name"]);
		
		//画像のファイルサイズと情報を取得する
		$ImageSize = $_FILES["Image"]["size"];
		$ImageInfo = getimagesize($_FILES["Image"]["tmp_name"]);
	
	break;
	
	//アップロード方式が設定されていない場合
	default:
		AddLogData("許可されていない方法でアップロードを要求されました","Error");
		echo 200;
		exit;
	break;
	
}

//画像サイズがアップロード可能サイズより大きい場合は弾く
if( $MaxSize < $ImageSize ){
	AddLogData("許可されていないファイルサイズのアップロードを要求されました","Error");
	echo 100;
	exit;
}

//取得した画像の情報を専用の変数に代入
$ImageWidth = $ImageInfo[0];
$ImageHeight = $ImageInfo[1];
$MIMETypeID = $ImageInfo[2];
$MIMEType = $ImageInfo["mime"];

//画像形式が GIF / JPEG /PNG 以外の場合は弾く
if(( $MIMETypeID != 1 )&&( $MIMETypeID != 2 )&&( $MIMETypeID != 3 )){
	echo 200;
	AddLogData("許可されていないファイルのアップロードを要求されました","Error");
	exit;
}

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

//ファイルパスを設定する（同じファイルが存在する時は名前を付け直す）
if( file_exists("./{$LogFolder}/{$FileName}.dat") ){
	sleep(1);
	$FileName = $FileBaseName.date("ymdHis");
	$UploadDate = date("ymd");
	$UploadTime = date("y/m/d H:i:s");
}

$ImageFileName = "{$FileName}.{$ExtensionID}";
$ImagePath = "./{$SaveFolder}/{$ImageFileName}";

//画像を保存する
if( $ExecuteType == "Enhance" ){
	file_put_contents($ImagePath,$UploadFileBin);
}else if( $ExecuteType == "Basic" ){
	move_uploaded_file($_FILES["Image"]["tmp_name"],$ImagePath);
}

//削除キーを取得する
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

//ファイルサイズを取得
$FileSizes = round( filesize($ImagePath)/1024 );

//パーミッション設定により画像が正しく表示されない場合は chmod関数 のコメントアウトを外して適切なパーミッションに設定してください
//chmod($ImagePath, 0606);
//chmod($ImageThumbPath, 0606); 
 
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
chmod($ImageDatPath, 0640); 

//画像一覧に追加する情報を保存する
$AddImageList = "{$ImageFileName}#{$UploadTime}#{$ImageWidth}#{$ImageHeight}#{$FileSizes}";

//結果用配列に代入する
$Result = $TransportURL.$ImageFileName;

//画像一覧を取得して追加された画像情報を追加する
$ImageListPath = "./{$LogFolder}/ImageList.txt";
$ImageList = file_get_contents($ImageListPath);
$ImageList = explode("\n",$ImageList);
if( empty( $ImageList ) ){
	$ImageList = $AddImageList;
}else{
	array_unshift($ImageList,$AddImageList);
}
file_put_contents($ImageListPath,implode("\n",$ImageList));

//エンハンスアップロードの場合は最後の１枚をアップロードし終えてから以下の処理をする
//※ダイアログアップロードの場合はアップロード待ちが無いのでそのまま処理を行う
if( $Uploading == $MaxBox ){
	
	//保存期間を超えた画像がある場合は削除する
	TimeLimitDeletion();
	
}

//ロックを解除して開放する
fclose($ProcessLock);

//結果を表示する
echo urlencode($Result);
exit;

?>
