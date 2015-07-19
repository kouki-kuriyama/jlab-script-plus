<?php

/*
	
	jlab-script-plus settings.php
	Version 0.06 dev4 / Kouki Kuriyama
	https://github.com/kouki-kuriyama/jlab-script-plus
	
	■ jlab-script-plus アップローダー設定ファイル ■
	　以下の項目を設定して、サーバーにアップロードしてください。
	　すべてのファイルをアップロード後、ブラウザから settings.php にアクセスします。
	　static フォルダに settings.dat が作成されれば設定完了です。
	　設定変更に settings.dat を削除する必要はありません。
	　詳しくは README.md をご覧ください。
	
*/

//管理者マスターキー
//　削除キーの暗号復元や、管理者による画像の削除に使用します。
//　必ず8文字以上の半角英数字を設定して下さい。
$MasterKey = 'MasterKey';

//管理者名
$Admin = '管理者◆TripKeys10';

//画像保存フォルダ(最後の / は不要です)
$SaveFolder = 's';

//サムネイル保存フォルダ(最後の / は不要です)
$ThumbSaveFolder = 't';

//ログファイル保存フォルダ(最後の / は不要です)
$LogFolder = 'd';

//実況ろだの絶対パス(http:// 若しくは https://から)
$FullURL = 'http://www.kurichans.jp/jlab-script-plus-v0064/';

//ファイル名接頭語(不要な場合は空欄にしてください)
$FileBaseName = 'developer';

//実況ろだのタイトル
$JlabTitle = '実況ろだTEST';

//画像の保存日数(日間)
$SaveDay = 0;

//画像の最大サイズ(キロバイト)
$MaxSize = 1024;

//サムネイル画像の最大横幅(ピクセル)
$MaxThumbWidth = 300;

//サムネイル画像の最大縦幅(ピクセル)
$MaxThumbHeight = 300;

//1ページに表示する画像の数
$DisplayImageCount = 10;

//ドラッグアンドドロップアップロードを有効にする
//　ドラッグアンドドロップアップロードを有効にすると、ブラウザ画面に画像をドラッグアンドドロップして画像を取り込むことができます。
//　この機能を有効にするには、動作中のPHPのバージョンが 5.4 以降である必要があります。
//　それよりも古いPHPバージョンで動作中の場合や、何らかの理由によりドラッグアンドドロップアップロードが使用できない場合は、自動的に無効になります。
// 　0で無効、1で有効になります。
$EnableDragDrop = 1;

//画像配信URLの変更
//mod_rewrite機能を使って画像配信URLを変更するときに設定します。特に使用しない場合は空欄にしてください。
//mod_rewrite機能の詳しい説明についてはApacheサイトや書籍等をご覧ください。
//http://jikkyo.org/jlab-script-plus/s/123.jpg を http://jikkyo.org/img/123.jpg にリライトして配信するには設定に $RewriteURL = 'http://jikkyo.org/img/'; と設定してください。
$RewriteURL = '';

/*
	
	jlab-script-plus の設定は以上です。
	この settings.php とそれ以外のファイルとをフォルダをサーバーにアップロードしてください。
	アップロード後、ブラウザから settings.php にアクセスしてアップローダーの設定を完了してください。
	
	［ｉ］これより下は設定用スクリプトです。
	　　　間違えて変更しないようにご注意ください。
	
*/

if( !file_exists("./static/settings.dat") ){

	//テキストで出力する
	header("Content-Type: text/plain; charset=UTF-8");

	$StaticDataFolder = "static";
	$SettingsFile = "./{$StaticDataFolder}/settings.dat";
	$ProcessFile = "./{$StaticDataFolder}/process.dat";
	
	if( !is_dir("./{$StaticDataFolder}/") ){
		echo "［！］static フォルダが存在しません。\n";
		exit;
	}
	
	if( !is_dir("./{$SaveFolder}/") ){
		echo "［！］{$SaveFolder} フォルダが存在しません。\n";
		exit;
	}
	
	if( !is_dir("./{$ThumbSaveFolder}/") ){
		echo "［！］{$ThumbSaveFolder} フォルダが存在しません。\n";
		exit;
	}
	
	if( !is_dir("./{$LogFolder}/") ){
		echo "［！］{$LogFolder} フォルダが存在しません。\n";
		exit;
	}
	
	if( !touch("./{$SaveFolder}/image-folder") ){
		echo "［！］{$SaveFolder} フォルダへ書き込みができません。\n";
		echo "　　　{$SaveFolder} フォルダのパーミッションをご確認ください。\n\n";
		exit;
	}
	
	if( !touch("./{$ThumbSaveFolder}/thumbnail-folder") ){
		echo "［！］{$ThumbSaveFolder} フォルダへ書き込みができません。\n";
		echo "　　　{$ThumbSaveFolder} フォルダのパーミッションをご確認ください。\n\n";
		exit;
	}
	
	if( !touch("./{$LogFolder}/log-folder") ){
		echo "［！］{$LogFolder} フォルダへ書き込みができません。\n";
		echo "　　　{$LogFolder} フォルダのパーミッションをご確認ください。\n\n";
		exit;
	}
	
	if(( !function_exists("getimagesizefromstring") )&&( $EnableDragDrop == 1 )){
		$EnableDragDrop = 0;
		echo "［！］動作中のPHPバージョンではドラッグアンドドロップ機能はお使い頂けません。\n";
		echo "　　　ドラッグアンドドロップ機能は PHP 5.4.0 以上でお使い頂けます。\n";
		echo "［ｉ］ドラッグアンドドロップ機能を無効にしました。\n\n";
	}else if(( function_exists("getimagesizefromstring") )&&( $EnableDragDrop == 1 )){
		echo "［ｉ］ドラッグアンドドロップ機能は有効に設定されています。\n\n";
	}else if(( function_exists("getimagesizefromstring") )&&( $EnableDragDrop == 0 )){
		echo "［ｉ］ドラッグアンドドロップ機能は無効に設定されています。\n";
		echo "　　　設定を変更することにより、ドラッグアンドドロップ機能を使用することができます。\n\n";
	}
	
	$AddSettingsData = $JlabTitle."\n";
	$AddSettingsData .= $Admin."\n";
	$AddSettingsData .= $SaveFolder."\n";
	$AddSettingsData .= $ThumbSaveFolder."\n";
	$AddSettingsData .= $LogFolder."\n";
	$AddSettingsData .= $FullURL."\n";
	$AddSettingsData .= $MaxSize."\n";
	$AddSettingsData .= $MaxThumbWidth."\n";
	$AddSettingsData .= $MaxThumbHeight."\n";
	$AddSettingsData .= $DisplayImageCount."\n";
	$AddSettingsData .= $SaveDay."\n";
	$AddSettingsData .= $FileBaseName."\n";
	$AddSettingsData .= $EnableDragDrop."\n";
	$AddSettingsData .= $RewriteURL;
	
	if( !touch($SettingsFile) ){
		echo "［！］settings.dat ファイルが作成できません。\n";
		echo "　　　static フォルダのパーミッションをご確認ください。\n\n";
		exit;
	}
	
	file_put_contents($SettingsFile, $AddSettingsData);
	file_put_contents($ProcessFile, "jlab-script-plus / process.dat");
	echo "［ｉ］settings.dat を保存しました。\n";
	echo "　　　index.php にアクセスしてアップローダーが正常に表示されるかご確認ください。\n\n";
	exit;

}

$DisplayMaxSize = $MaxSize;
$MaxSize = $MaxSize * 1024;

if( $RewriteURL != "" ){
	$TransportURL = $RewriteURL;
}else{
	$TransportURL = "{$FullURL}{$SaveFolder}/";
}

if(( function_exists("getimagesizefromstring") )&&( $EnableDragDrop == 1 )){
	$UseDragDrop = "true";
}else{
	$UseDragDrop = "false";
}

//↓各PHPファイルへ戻る

?>