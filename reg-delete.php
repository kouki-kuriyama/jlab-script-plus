<?php
/*
	■ 定期削除用スクリプト ■
	定期削除用スクリプトです。CronJob等で1日1回アクションを起こすと、保存期間以前にアップロードされた
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
	$FileBaseName = $SettingData[13];
	
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

//期限を取得
$SaveDayOver = date("ymd",strtotime("- {$SaveDay} days"));

//一覧ログを取得する
$ImageList = file_get_contents("./{$LogFolder}/ImageList.txt");
$ImageList = explode("\n",$ImageList);

//画像フォルダをスキャン
$DeleteImage = scandir("./{$SaveFolder}");

//削除する
foreach($DeleteImage as $ImageNameKey => $ImageNameValue) {

	//「.」と「..」の場合はcontinue
	if(( $ImageNameValue = "." )||( $ImageNameValue = ".." )){
		continue;
	}

	//拡張子と接頭語を取り外す(アップロード時間を取得)
	$ImageName_Num = preg_replace("~[^0-9]~","",$ImageNameValue);
	
	//アップロード日を取得
	$UploadedDay = substr($ImageName_Num,0,6);
	
	//もしアップロード日が保存期間を超えていたら、削除する
	if( $UploadedDay < $SaveDayOver ){
	
		//画像・サムネイル・ログファイルを削除
		unlink("./{$SaveFolder}/{$ImageNameValue}");
		unlink("./{$ThumbSaveFolder}/{$ImageNameValue}");
		unlink("./{$LogFolder}/{$FileBaseName}{$ImageName_Num}.dat");
		echo "［情報］{$ImageNameValue}を削除\n";
		
		//画像一覧ログから削除する
		$ImageListEdited = true;
		foreach($ImageList as $ImageNumKey => $ImageNumValue) {
			if( preg_match("~{$ImageNameValue}~",$ImageNumValue) ) {
				break;
			}
		}
		unset($ImageList[$ImageNumKey]);
	
	}

}

//画像一覧ログに変更があったら新しく保存
if( $ImageListEdited ){
	file_put_contents("./{$LogFolder}/ImageList.txt",implode("\n",$ImageList));
	echo "［情報］ 画像一覧ログ保存\n";
}else{
	echo "［情報］画像一覧ログ変更なし\n";
}

//完了メッセージ
echo "［情報］完了";
exit;
?>