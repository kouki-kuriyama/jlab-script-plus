<?php
/*
	■ 定期削除用スクリプト ■
	定期削除用スクリプトです。CronJob等で1日1回アクションを起こすと、１週間前にアップロードされた
	画像・サムネイル・ログファイルを自動的に削除します。
	CronJobが使用できない場合は、このスクリプトに直接ブラウザでアクセスしても削除することができます。
	
*/

header("Content-Type:text/plain");

//設定ファイルの読み込みをする
if( file_exists("./static-data/setting.dat") ){
	$SettingsData = file_get_contents("./static-data/setting.dat");
	$SettingData = explode("\n",$SettingsData);

	$SaveFolder = $SettingData[2];
	$ThumbSaveFolder = $SettingData[3];
	$LogFolder = $SettingData[4];

}else{
	echo "設定ファイルがありません";
	exit;
}

//1週間前(8日前)の日付を取得する
$DelDate = date("ymd", strtotime("- 8 days"));

//すべてのフォルダをスキャン
$DeleteImage = scandir("./{$SaveFolder}");
$DeleteThumb = scandir("./{$ThumbSaveFolder}");
$DeleteLog = scandir("./{$LogFolder}");

//ログファイルを確認
if( !file_exists("./{$LogFolder}/ImageList-{$DelDate}.txt")){
	echo "ログファイルがありません";
	exit;
}

//削除する
foreach($DeleteImage as $IKey => $IValue) {
	if( preg_match("~^{$DelDate}~",$IValue) ){
		unlink("./{$SaveFolder}/{$DeleteImage[$IKey]}");
		echo "- 削除 : {$DeleteImage[$IKey]}\n";
	}
}

foreach($DeleteThumb as $TKey => $TValue) {
	if( preg_match("~^{$DelDate}~",$TValue) ){
		unlink("./{$ThumbSaveFolder}/{$DeleteThumb[$TKey]}");
		echo "- 削除 : {$DeleteThumb[$TKey]}\n";
	}
}

foreach($DeleteLog as $LKey => $LValue) {
	if( preg_match("~^{$DelDate}~",$LValue) ){
		unlink("./{$LogFolder}/{$DeleteLog[$LKey]}");
		echo "- 削除 : {$DeleteLog[$LKey]}\n";
	}
}

unlink("./{$LogFolder}/ImageList-{$DelDate}.txt");
echo "- 削除 : ImageList-{$DelDate}.txt\n";
echo "- 完了";

exit;
?>