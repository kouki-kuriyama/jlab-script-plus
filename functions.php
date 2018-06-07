<?php

/*
	
	jlab-script-plus functions.php
	Version 0.07 / Kouki Kuriyama
	https://github.com/kouki-kuriyama/jlab-script-plus
	
*/

//期限切れの画像をすべて削除する
function TimeLimitDeletion($Force){
	
	global $FileBaseName,$SaveFolder,$ThumbSaveFolder,$LogFolder,$SaveDay,$ImageList,$ImageListPath;
	
	//今日の日付を取得する
	$Today = date("ymd");
	
	//今日削除した記録が無い・メガエディターから強制削除の場合
	if(( !file_exists("./static/deleted-{$Today}.dat"))||( $Force == true )){
		
		//削除日の設定
		$Deleted = false;
		$Yesterday = date("ymd",strtotime("- 1 days"));
		$SaveDayOver = date("ymd",strtotime("- {$SaveDay} days"));
		
		//画像本体・ログ・サムネイルをすべて削除
		$DeleteImages = scandir("./{$SaveFolder}");
		foreach($DeleteImages as $ImageNameKey => $ImageNameValue) {
		
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
						$Deleted = true;
						unset($ImageList[$ImageListNumKey]);
						break;
					}
				}
			}
		}
		
		//画像一覧ログを再度保存する(変更があった場合のみ)
		if( $Deleted ){
			file_put_contents($ImageListPath,implode("\n",$ImageList));
		}
		
		//古い記録ファイルを削除する
		foreach( glob("./static/deleted-[0-9]*.dat") as $DeletedRecFile ){
			unlink($DeletedRecFile);
		}
		
		//記録ファイルの名前を書き換える
		$ExecTime = date("y/m/d H:i:s");
		AddLogData("期限切れの画像が消去されました","Info");
		file_put_contents("./static/deleted-{$Today}.dat","jlab-script-plus[Info] : 期限切れの画像が消去されました（{$ExecTime}）");
		
	}
	
	return;
}

//jlab-script-plus全体ログに書き留める
function AddLogData($LogData,$NameTag){
	
	//現在時間を取得する
	$ExecTime = date("y/m/d H:i:s");
	
	//ログデータが存在しない場合は作成する
	if( !file_exists("./static/jlab-script-plus.dat")){
		file_put_contents("./static/jlab-script-plus.dat","jlab-script-plus[Info] : ログファイルを作成しました（{$ExecTime}）");
		chmod("./static/jlab-script-plus.dat",0640);
	}
	
	//ログデータを書き留める
	$SavedLogData = file_get_contents("./static/jlab-script-plus.dat");
	file_put_contents("./static/jlab-script-plus.dat","jlab-script-plus[{$NameTag}] : {$LogData}（{$ExecTime}）\n{$SavedLogData}");
	
	return;
	
}
?>