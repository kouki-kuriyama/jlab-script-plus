<?php
/*
	■ 定期削除用スクリプト ■
	定期削除用スクリプトです。CronJob等で1日1回アクションを起こすと、１週間前にアップロードされた
	画像・サムネイル・ログファイルを自動的に削除します。
	CronJobが使用できない場合は、このスクリプトに直接ブラウザでアクセスしても削除することができます。
	※マニュアル削除が有効になっている場合はこのスクリプトは動作しません。
	
*/

header("Content-Type:text/plain");

//設定ファイルの読み込みをする
if( file_exists("./static-data/setting.dat") ){
	$SettingsData = file_get_contents("./static-data/setting.dat");
	$SettingData = explode("\n",$SettingsData);

	$SaveFolder = $SettingData[2];
	$ThumbSaveFolder = $SettingData[3];
	$LogFolder = $SettingData[4];
	$SaveDay = $SettingData[10];
	$ManualDelete = $SettingData[11];
	
}else{
	echo "設定ファイルがありません。";
	exit;
}

/*//マニュアル削除が有効な場合は操作を取り消す
if( $ManualDelete == 1 ){
	echo "マニュアル削除が有効になっています。\n";
	echo "定期削除を有効にする場合はマニュアル削除を無効にしてください。";
	exit;
}
*/

$SnDay = $SaveDay + 1;
$DelDate = date("ymd", strtotime("- {$SnDay} days"));

//すべてのフォルダをスキャン
$DeleteImage = scandir("./{$SaveFolder}");
$DeleteThumb = scandir("./{$ThumbSaveFolder}");
$DeleteLog = scandir("./{$LogFolder}");

//ログファイルを確認
if( !file_exists("./{$LogFolder}/ImageList-{$DelDate}.txt")){
	echo "{$SnDay}日前のログファイルがありません";
	exit;
}

//削除する
foreach($DeleteImage as $IKey => $IValue) {
	if( preg_match("~^(.*){$DelDate}~",$IValue) ){
		unlink("./{$SaveFolder}/{$DeleteImage[$IKey]}");
		echo "- 削除 : {$DeleteImage[$IKey]}\n";
	}
}

foreach($DeleteThumb as $TKey => $TValue) {
	if( preg_match("~^(.*){$DelDate}~",$TValue) ){
		unlink("./{$ThumbSaveFolder}/{$DeleteThumb[$TKey]}");
		echo "- 削除 : {$DeleteThumb[$TKey]}\n";
	}
}

foreach($DeleteLog as $LKey => $LValue) {
	if( preg_match("~^(.*){$DelDate}~",$LValue) ){
		unlink("./{$LogFolder}/{$DeleteLog[$LKey]}");
		echo "- 削除 : {$DeleteLog[$LKey]}\n";
	}
}

unlink("./{$LogFolder}/ImageList-{$DelDate}.txt");
echo "- 削除 : ImageList-{$DelDate}.txt\n";

$ImageListALLPath = "./{$LogFolder}/ImageList-all.txt";
$ImageListALL = file_get_contents($ImageListALLPath);
$ImageListALL_array = explode("\n",$ImageListALL);
foreach($ImageListALL_array as $LAKey => $LAValue) {
	if( preg_match("~^(.*){$DelDate}~",$LAValue) ){
		unset($ImageListALL_array[$LAKey]);
	}
}

file_put_contents($ImageListALLPath,implode("\n",$ImageListALL_array));
echo "- 一覧ログ整合 : ImageList-all.txt\n";

echo "- 完了";
exit;
?>