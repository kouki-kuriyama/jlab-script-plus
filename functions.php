<?php

/*
	
	jlab-script-plus functions.php
	Version 0.06 dev4 / Kouki Kuriyama
	https://github.com/kouki-kuriyama/jlab-script-plus
	
*/

//期限切れの画像をすべて削除する
function TimeLimitDeletion($Force){

	global $FileBaseName,$SaveFolder,$ThumbSaveFolder,$LogFolder,$SaveDay,$ImageList,$ImageListPath;

	$Deleted = false;
	$Today = date("ymd");
	$Yesterday = date("ymd",strtotime("- 1 days"));
	$SaveDayOver = date("ymd",strtotime("- {$SaveDay} days"));
	
	//今日削除した記録が無い・メガエディターから強制削除の場合
	if(( !file_exists("./static/deleted-{$Today}.dat"))||( $Force == true )){
	
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
		
		//記録ファイルの名前を書き換える
		if( file_exists("./static/deleted-{$Yesterday}.dat") ){
			rename("./static/deleted-{$Yesterday}.dat","./static/deleted-{$Today}.dat");
		}else if( !file_exists("./static/deleted-{$Today}.dat") ){
			file_put_contents("./static/deleted-{$Today}.dat","jlab-script-plus deleted-manage.dat");
		}
	}
	
	return;
}
?>