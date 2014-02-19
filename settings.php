<?php
/*
	
	・jlab-script-plus settings.php
	　Version 0.01 / Kouki Kuriyama
	　http://github.com/kouki-kuriyama/jlab-script-plus
	
	■ jlab-script-plusスクリプト設定ファイル ■
	以下の項目を設定して、サーバにアップロードしてください。
	アップロード後 settings.php にブラウザでアクセスします。
	static-data フォルダに setting.dat が作成されれば設定完了です。
	設定を変更するには setting.dat を削除する必要があります。
	詳しくは README.md をご覧ください。
*/

//管理者名
$Admin = '管理者◆TripKeys10';

//画像保存フォルダ(最後の / は不要です)
$SaveFolder = 's';

//サムネイル保存フォルダ(最後の / は不要です)
$ThumbSaveFolder = 't';

//ログファイル保存フォルダ(最後の / は不要です)
$LogFolder = 'd';

//実況ろだの絶対パス(http://から)
$FullURL = 'http://jikkyo.org/jlab-test/';

//実況ろだのタイトル
$JlabTitle = '実況ろだTEST';

//画像の最大サイズ(キロバイト)
$MaxSize = '1024';

//サムネイル画像の最大横幅(ピクセル)
$MaxThumbWidth = '200';

//サムネイル画像の最大縦幅(ピクセル)
$MaxThumbHeight = '200';

//1ページに表示する画像の数
$DisplayImageCount = '10';

//保存日数
$SaveDay = "7";

//管理者マスターキー
//削除キーの暗号復元や、管理者による画像の削除に使用します。
//必ず8文字以上の半角英数字を設定して下さい。
//ここに設定したマスターキーを同梱の masterkey.php にも設定してください。
$MasterKey = 'MasterKey';

/*

	設定は以上です。
	このファイルをサーバにアップロードし、ブラウザよりアクセスして下さい。
	これより下は設定用の実行スクリプトです。

*/

header("Content-Type:text/plain");

$StaticDataFolder = "./static-data";
$SettingFile = "{$StaticDataFolder}/setting.dat";

if( !is_dir("{$StaticDataFolder}/") ){
	echo "Error : static-data フォルダが存在しません。\n";
	exit;
}

if( file_exists($SettingFile) ){
	echo "Error : 設定ファイルを削除して下さい。\n";
	exit;
}

if( !is_dir("./{$SaveFolder}/") ){
	echo "Error : {$SaveFolder} フォルダが存在しません。\n";
	exit;
}

if( !is_dir("./{$ThumbSaveFolder}/") ){
	echo "Error : {$ThumbSaveFolder} フォルダが存在しません。\n";
	exit;
}

if( !is_dir("./{$LogFolder}/") ){
	echo "Error : {$LogFolder} フォルダが存在しません。\n";
	exit;
}

$MaxSize = $MaxSize * 1024;

$SettingData .= $JlabTitle."\n";
$SettingData .= $Admin."\n";
$SettingData .= $SaveFolder."\n";
$SettingData .= $ThumbSaveFolder."\n";
$SettingData .= $LogFolder."\n";
$SettingData .= $FullURL."\n";
$SettingData .= $MaxSize."\n";
$SettingData .= $MaxThumbWidth."\n";
$SettingData .= $MaxThumbHeight."\n";
$SettingData .= $DisplayImageCount."\n";
$SettingData .= $SaveDay;

file_put_contents( $SettingFile,$SettingData );
echo "設定が完了しました。\n";
echo "[ masterkey.php ]に設定したマスターキーを記入してアップロードして下さい。";
exit;

?>
