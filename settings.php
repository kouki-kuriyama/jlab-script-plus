<?php
/*
	
	・jlab-script-plus settings.php
	　Version 0.05a / Kouki Kuriyama
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
$FullURL = 'http://www.jikkyo.org/jlab-script-plus/';

//ファイル名接頭語(不要な場合は空欄にしてください)
$FileBaseName = '';

//実況ろだのタイトル
$JlabTitle = '実況ろだTEST';

//画像の最大サイズ(キロバイト)
$MaxSize = '1024';

//サムネイル画像の最大横幅(ピクセル)
$MaxThumbWidth = '250';

//サムネイル画像の最大縦幅(ピクセル)
$MaxThumbHeight = '250';

//1ページに表示する画像の数
$DisplayImageCount = '10';

//ドラッグアンドドロップを有効にする
//ドラッグアンドドロップによるアップロードを有効にするには、PHPのバージョンが 5.4 以降である必要があります。
//それよりも古いPHPバージョンの場合、自動的に無効になります。
// 0で無効、1で有効になります。
$UseDragDrop = 1;

//保存日数
$SaveDay = '5';

//マニュアル削除
//マニュアル削除は、CronJobで reg-delete.php を定期的に動かせない場合や、CronJobのやり方が分からない場合は有効にしてください。
//有効にすると、今日の1枚目がアップロードされると同時に保存期間を超えた画像が削除されます。reg-delete.php による定期削除は無効になります。
// 0で無効、1で有効になります。
$ManualDelete = 1;

//削除キーの暗号化をパスする
//サーバーの仕様により削除キーの暗号化を有効にするとエラーが発生する場合に有効にします。
//この設定を有効にすると、削除キーはログファイルに平文で保存されます。
//ログファイルにURL直打ちでログファイルにアクセスすると削除キーが閲覧できてしまう為、必ず付属の htaccessファイルをアップロードして .dat ファイルにアクセスできないようにしてください。
// 0で無効、1で有効になります。
$DelKeyByPass = 0;

//画像配信URLの変更
//mod_rewrite機能を使って画像配信URLを変更するときに設定します。特に使用しない場合は空欄にしてください。
//mod_rewite機能の詳しい説明についてはApacheサイト等でご覧ください。
//http://jikkyo.org/jlab-script-plus/s/123.jpg を http://jikkyo.org/img/123.jpg にrewriteして配信するには設定に $RewriteURL = 'http://jikkyo.org/img/'; と設定してください。
$RewriteURL = '';

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
	echo "［エラー］static-data フォルダが存在しません。\n";
	exit;
}

if( file_exists($SettingFile) ){
	echo "［エラー］設定ファイルを削除して下さい。\n";
	exit;
}

if( !is_dir("./{$SaveFolder}/") ){
	echo "［エラー］{$SaveFolder} フォルダが存在しません。\n";
	exit;
}

if( !touch("./{$SaveFolder}/src.access") ){
	echo "［エラー］{$SaveFolder} フォルダのパーミッションをご確認ください。\n\n";
	exit;
}

if( !is_dir("./{$ThumbSaveFolder}/") ){
	echo "［エラー］{$ThumbSaveFolder} フォルダが存在しません。\n";
	exit;
}

if( !touch("./{$ThumbSaveFolder}/thumb.access") ){
	echo "［エラー］{$ThumbSaveFolder} フォルダのパーミッションをご確認ください。\n\n";
	exit;
}

if( !is_dir("./{$LogFolder}/") ){
	echo "［エラー］{$LogFolder} フォルダが存在しません。\n";
	exit;
}

if( !touch("./{$LogFolder}/log.access") ){
	echo "［エラー］{$LogFolder} フォルダのパーミッションをご確認ください。\n\n";
	exit;
}

if(( version_compare(PHP_VERSION,'5.4.0') < 0 )||( $UseDragDrop == 1 )){
	echo "［情報］PHPバージョンが 5.4.0 以上です。ドラッグアンドドロップによるアップロードは有効です。\n\n";
}else{
	$UseDragDrop = 0;
	echo "［注意］PHPバージョンが 5.4.0 以下の為、ドラッグアンドドロップによるアップロードは無効です。\n\n";
}

if( empty( $RewriteURL )){
	$TransportURL = "{$FullURL}{$SaveFolder}/";
}else{
	$TransportURL = $RewriteURL;
}

if( touch("./static-data/upd-manage.dat") ){
	echo "［情報］upd-manage.dat を作成しました\n\n";
}else{
	echo "［エラー］upd-manage.dat が作成できません。static-dataフォルダのパーミッションをご確認ください。\n\n";
	exit;
}

$MaxSize = $MaxSize * 1024;

$SettingData = $JlabTitle."\n";
$SettingData .= $Admin."\n";
$SettingData .= $SaveFolder."\n";
$SettingData .= $ThumbSaveFolder."\n";
$SettingData .= $LogFolder."\n";
$SettingData .= $FullURL."\n";
$SettingData .= $MaxSize."\n";
$SettingData .= $MaxThumbWidth."\n";
$SettingData .= $MaxThumbHeight."\n";
$SettingData .= $DisplayImageCount."\n";
$SettingData .= $SaveDay."\n";
$SettingData .= $ManualDelete."\n";
$SettingData .= $DelKeyByPass."\n";
$SettingData .= $FileBaseName."\n";
$SettingData .= $UseDragDrop."\n";
$SettingData .= $TransportURL;

if( !touch("./static-data/upd-manage.dat") ){
	echo "［エラー］setting.dat が作成できません。static-dataフォルダのパーミッションをご確認ください。\n\n";
	exit;
}

file_put_contents( $SettingFile,$SettingData );
echo "［情報］設定が完了しました。\n\n";
if( $DelKeyByPass == 1 ){
	echo "［情報］masterkey.php に設定したマスターキーを記入してアップロードして下さい。\n\n";
	echo "［注意］削除キーを平文で保存します。\n";
	echo "　　　　必ず付属の .htaccessファイル を設置してください。";
}else{
	echo "［情報］masterkey.php に設定したマスターキーを記入してアップロードして下さい。\n";
}

exit;

?>
