<?php

/*
	
	jlab-script-plus mega-editor.php
	Version 0.07 / Kouki Kuriyama
	https://github.com/kouki-kuriyama/jlab-script-plus
	
*/

//保険
$MegaEditor = false;

//設定とThumb.phpを読み込む
require_once("./settings.php");
require_once("./static/Thumb.php");

//関数を読み込む
require_once("./functions.php");

//引数取得
$DisplayDay = (string)$_GET["Day"];
$CurrentPage = $_GET["Page"];

//送信されるすべてのクエリを読み込み
$LoginEditor = $_COOKIE["MEditor"];
$POSTMasterKey = $_POST["getMasterkey"];

//ログインクッキーとマスターキーが送信されていない場合はログイン画面を表示
if(( empty($LoginEditor) )&&( empty($POSTMasterKey) )){
	$MegaEditor = false;
	$MainSetHTML .= "<div style=\"text-align:center\">\n";
	$MainSetHTML .= "管理者用マスターキーを入力してください\n";
	$MainSetHTML .= "<form method=\"post\" action=\"mega-editor.php\" name=\"LoginMegaEditor\">\n";
	$MainSetHTML .= "<div style=\"margin-top:1em\"><input type=\"password\" style=\"width:300px\" name=\"getMasterkey\" class=\"TextBox\"></div>\n";
	$MainSetHTML .= "<div style=\"margin-top:1em\"><input type=\"submit\" class=\"BlueButton\" value=\"ログイン\"> <input type=\"button\" class=\"RedButton\" value=\"終了\" onclick=\"location.href='./'\"></div>\n";
	$MainSetHTML .= "</form>\n";
	$MainSetHTML .= "</div>\n\n";
}

//マスターキーのみが送信されている場合
else if(( empty($LoginEditor) )&&( !empty($POSTMasterKey) )){

	//マスターキーが正しい場合はメガエディターへ
	if( $MasterKey == $POSTMasterKey ){
	
		//6時間有効のCookie
		$MegaEditor = true;
		setcookie("MEditor","Login", time()+21600);
		$MainSetHTML .= "メガエディターではアップロードされた画像を管理することができます。<br>\n";
		$MainSetHTML .= "アップローダーの設定・マスターキーの変更は settings.php ファイル本体を編集する必要があります。<br>\n";
	
	}else{
		$MegaEditor = false;
		$MainSetHTML .= "<div style=\"text-align:center\">\n";
		$MainSetHTML .= "<span style=\"font-weight:bold; color:red\">管理者用マスターキーが間違っています</span><br>\n";
		$MainSetHTML .= "管理者用マスターキーを入力してください\n";
		$MainSetHTML .= "<form method=\"post\" action=\"mega-editor.php\" name=\"LoginMegaEditor\">\n";
		$MainSetHTML .= "<div style=\"margin-top:1em\"><input type=\"password\" style=\"width:300px\" name=\"getMasterkey\" class=\"TextBox\"></div>\n";
		$MainSetHTML .= "<div style=\"margin-top:1em\"><input type=\"submit\" class=\"BlueButton\" value=\"ログイン\"> <input type=\"button\" class=\"RedButton\" value=\"終了\" onclick=\"location.href='./'\"></div>\n";
		$MainSetHTML .= "</form>\n";
		$MainSetHTML .= "</div>\n\n";
	}

}

//ログイン許可
else if( $LoginEditor == "Login" ){
	$MegaEditor = true;
	$MainSetHTML .= "メガエディターではアップロードされた画像を管理することができます。<br>\n";
	$MainSetHTML .= "アップローダーの設定・マスターキーの変更はファイル本体を編集する必要があります。<br>\n";
	
	//すべてのクエリを読み込む
	$ArcData = $_GET["Arc"];
	$EditMode = $_GET["EditMode"];
	
	if( !empty($EditMode) ){
	
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//モード処理(画像のダウンロード)
		if(( $EditMode == "Dl" )&&( !empty($ArcData) )){
		
			//ファイルが存在するかを確認
			if( file_exists("./{$SaveFolder}/{$ArcData}") ){
			
				//画像ファイル名
				$DLFileName = "./{$SaveFolder}/{$ArcData}";
				
				//ダウンロード
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename='{$ArcData}'"); 
				header("Content-Length: ".filesize($DLFileName));
				readfile($DLFileName);
				exit;
				
			}else{
				//メッセージ
				$MainSetHTML = "<span style=\"font-weight:bold; color:red\">{$ArcData} は見つかりませんでした</span>\n";
			}
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//モード処理(ログのダウンロード)
		else if(( $EditMode == "LogDl" )&&( !empty($ArcData) )){
			
			//ファイルが存在するかを確認
			if( file_exists("./{$SaveFolder}/{$ArcData}") ){
			
				//ファイル名分割
				list($LogFName,$Trash) = explode(".",$ArcData);
				
				//ログファイル名
				$DLFileName = "./{$LogFolder}/{$LogFName}.dat";
				
				//ダウンロード
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename='{$LogFName}.txt'");
				header("Content-Length: ".filesize($DLFileName));
				readfile($DLFileName);
				exit;
				
			}else{
				//メッセージ
				$MainSetHTML = "<span style=\"font-weight:bold; color:red\">{$ArcData} のログファイルは見つかりませんでした</span>\n";
			}
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//モード処理(全体ログのダウンロード)
		else if( $EditMode == "AllLogDl" ){
		
			//ダウンロード
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename='jlab-script-plus.txt'");
			header("Content-Length: ".filesize("./static/jlab-script-plus.dat"));
			readfile("./static/jlab-script-plus.dat");
			exit;
		
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//モード処理(画像削除)
		else if(( $EditMode == "Del" )&&( !empty($ArcData) )){
		
			//ファイルが存在するかを確認
			if( file_exists("./{$SaveFolder}/{$ArcData}") ){
				
				//拡張子とファイル名を分割
				list($RFileName,$ExtensionID) = explode(".",$ArcData);
				
				//拡張子と接頭語を取り外す
				$NDFileName_oq = preg_replace("~[^0-9]~","",$RFileName);
				
				//アップロード日を取得する
				$UploadedDate = substr($NDFileName_oq,0,6);
				
				//管理者権限で削除
				unlink("./{$SaveFolder}/{$ArcData}");
				unlink("./{$ThumbSaveFolder}/{$ArcData}");
				unlink("./{$LogFolder}/{$RFileName}.dat");
				
				//一覧ログから削除する
				$ImageList = file_get_contents("./{$LogFolder}/ImageList.txt");
				$ImageList = explode("\n",$ImageList);
				foreach($ImageList as $key => $value) {
					if( preg_match("~{$ArcData}~",$value) ) {
						break;
					}
				}
				unset($ImageList[$key]);
				file_put_contents("./{$LogFolder}/ImageList.txt",implode("\n",$ImageList));
				
				//メッセージ
				$MainSetHTML = "<span style=\"font-weight:bold; color:blue\">{$ArcData} は削除されました</span>\n";
	
			}else{
				//メッセージ
				$MainSetHTML = "<span style=\"font-weight:bold; color:red\">{$ArcData} は見つかりませんでした</span>\n";
			}
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//ログをクイックルック
		else if(( $EditMode == "qLook" )&&( !empty($ArcData) )){
		
			//ファイルが存在するかを確認
			if( file_exists("./{$SaveFolder}/{$ArcData}") ){
				
				//拡張子とファイル名を分割
				list($LogFName,$ExtensionID) = explode(".",$ArcData);
			
				$DLFileName = "./{$LogFolder}/{$LogFName}.dat";
				header("Content-type: text/plain");
				readfile($DLFileName);
				exit;
	
			}else{
				//メッセージ
				$MainSetHTML = "<span style=\"font-weight:bold; color:red\">{$ArcData} は見つかりませんでした</span>\n";
			}
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//イメージリストをクイックルック
		else if( $EditMode == "qLookList" ){
			
			//ファイルが存在するかを確認
			if( file_exists("./{$LogFolder}/ImageList.txt") ){
				header("Content-type: text/plain");
				readfile("./{$LogFolder}/ImageList.txt");
				exit;
			}else{
				header("Content-type: text/plain");
				echo "ログはありません";
				exit;
			}
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//ログを再生成する
		else if( $EditMode == "RestoreLog" ){
			
			//ログファイル名
			$LogFileName = "./{$LogFolder}/ImageList.txt";
			
			//ログが保存済みの場合は一度削除する
			if( file_exists("./{$LogFileName}") ){
				unlink($LogFileName);
			}
			
			//ファイルリストの配列を作成する
			$FileNamelist = array();
			
			//画像一覧を取得する
			if( $GetObFolder = opendir("./{$SaveFolder}/") ){
				
				//保存フォルダを走査する
				while(( $NDFileName = readdir($GetObFolder) ) !== false) {
					
					//フォルダ内の画像データを調査し、配列に代入する
					if(( $NDFileName != "." )&&( $NDFileName != ".." )){
						
						//拡張子と接頭語を取り外す
						$NDFileName_oq = preg_replace("~[^0-9]~","",$NDFileName);
						
						//画像IDからアップロード時間を取得
						$UpYear = substr($NDFileName_oq,0,2);
						$UpMonth = substr($NDFileName_oq,2,2);
						$UpDay = substr($NDFileName_oq,4,2);
						$UpHour = substr($NDFileName_oq,6,2);
						$UpMinute = substr($NDFileName_oq,8,2);
						$UpSecond = substr($NDFileName_oq,10,2);
						
						//画像から縦横幅、サイズを取得する
						list($ImageWidth,$ImageHeight,$MType,$Attr) = getimagesize("./{$SaveFolder}/{$NDFileName}");
						$FileSizes = round( filesize("./{$SaveFolder}/{$NDFileName}")/1024 );
						
						$FileNamelist[] = $NDFileName."#{$UpYear}/{$UpMonth}/{$UpDay} {$UpHour}:{$UpMinute}:{$UpSecond}#{$ImageWidth}#{$ImageHeight}#{$FileSizes}";
						
					}
				}
				
				//フォルダ走査を終了する
				closedir($GetObFolder);
			}
			
			//新しい順にソートする
			arsort($FileNamelist);
			
			//imagelist.txtに保存する
			file_put_contents("{$LogFileName}",implode("\n",$FileNamelist));
			
			//メッセージ
			$MainSetHTML = "<span style=\"font-weight:bold; color:blue\">ログファイルをリストアしました</span>\n";
			$DisplayDay = $ArcData;
			
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//保存期間が終了している画像・ログを一括削除
		else if( $EditMode == "CleanUpExp" ){
			
			//一覧ログを取得する
			$ImageList = file_get_contents("./{$LogFolder}/ImageList.txt");
			$ImageList = explode("\n",$ImageList);
			
			//(この処理は functions.php に移動しました)
			TimeLimitDeletion(true);
			
			$MainSetHTML = "<span style=\"font-weight:bold; color:blue\">期限切れの画像・ログファイルを削除しました</span>\n";
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//すべてのサムネイルを再生成
		else if( $EditMode == "RemakeThumb" ){
			
			if( $GetTObFolder = opendir("./{$SaveFolder}/") ){
				
				//保存フォルダを走査する
				while(( $NDFileNameThumb = readdir($GetTObFolder) ) !== false) {
					
					//フォルダ内の画像データを調査しサムネイルを作成する
					//サムネイルサイズはsetting.datに設定された値を使用
					if(( $NDFileNameThumb != "." )&&( $NDFileNameThumb != ".." )){
						
						//古いサムネイルを削除する
						unlink("./{$ThumbSaveFolder}/{$NDFileNameThumb}");
						
						//拡張子を取り除く
						list($ReFileName,$Trash) = explode(".",$NDFileNameThumb);
						
						$RemakeThumb = new Image("./{$SaveFolder}/{$NDFileNameThumb}");
						$RemakeThumb -> name("../{$ThumbSaveFolder}/{$ReFileName}");
						$RemakeThumb -> width($MaxThumbWidth);
						$RemakeThumb -> save();
						
					}
				}
			
				//フォルダ走査を終了する
				closedir($GetTObFolder);
			
			}
			
			$MainSetHTML = "<span style=\"font-weight:bold; color:blue\">サムネイルを再生成しました</span>\n";
			
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
		
		//メガエディターからログアウト
		else if( $EditMode == "Logout" ){
			$MegaEditor = false;
			setcookie("MEditor","Login", time()-3600);
			header("Location:./");
			exit;
		}
		
		/* *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#* */
	}

}

?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title>メガエディター : <?php echo $JlabTitle; ?></title>

<!-- Default CSS/Javascript -->
<link type="text/css" rel="stylesheet" href="./static/jlab-script-plus.css">
<script type="text/javascript" src="./static/jlab-script-plus.js"></script>

<!-- CSS -->
<style type="text/css">
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

#LinkMenu li {
	float:left;
	margin:0px 10px 0px 0px;
}
</style>

<?php
if( $MegaEditor ){
?>
<script type="text/javascript">

//画像を削除する
function DeleteImage(ImgName){
	if( window.confirm(ImgName + "を削除します\n(※ログファイルも削除する為、ログファイルのダウンロードをおすすめします)") ){
		location.href="./mega-editor.php?EditMode=Del&Arc=" + ImgName + "";
	}
}

//ログを再生成する
function RestoreLog(){
	if( window.confirm("一覧ログをリストアします") ){
		location.href = "./mega-editor.php?EditMode=RestoreLog";
	}
}

//サムネイルの再生成をする
function RemakeThumb(){
	if( window.confirm("すべての画像のサムネイルを再生成します\nサムネイル幅：<?php echo $MaxThumbWidth; ?>px\nサムネイル高：<?php echo $MaxThumbHeight; ?>px\n(変更する場合はsettings.phpで再設定してください)")){
		location.href = "./mega-editor.php?EditMode=RemakeThumb";
	}
}

//期限切れの画像・ログを削除する
function CleanUpExp(){
	if( window.confirm("期限切れの画像・ログを削除します")){
		location.href = "./mega-editor.php?EditMode=CleanUpExp";
	}
}

</script>
<?php
}
?>

</head>
<body>

<!-- Header -->
<header>
<h1>メガエディター : <?php echo $JlabTitle; ?></h1>
</header>

<!-- Contents -->
<div id="Contents">

<!-- MainSet -->
<div id="ResultP" style="text-align:left !important;">
<?php echo $MainSetHTML; ?>
</div>

<!-- LinkMenu -->
<div id="LinkMenu">
<ul style="padding:0; list-style:none;">
<?php
if( $MegaEditor ){
	echo "<li><a href=\"mega-editor.php?EditMode=qLookList\" target=\"_blank\">ログをクイックルック</a></li>\n";
	echo "<li><a href=\"mega-editor.php\" onclick=\"RestoreLog(); return false;\">ログをリストア</a></li>\n";
	echo "<li><a href=\"mega-editor.php?\" onclick=\"RemakeThumb(); return false;\">すべてのサムネイルを再生成</a></li>\n";
	echo "<li><a href=\"mega-editor.php\" onclick=\"CleanUpExp(); return false;\">期限切れの画像を一括削除</a></li>\n";
	echo "<li><a href=\"mega-editor.php?EditMode=AllLogDl\">スクリプトログをダウンロード</a></li>\n";
	echo "<li><a href=\"?EditMode=Logout\">ログアウト</a></li>\n";
}else{
	echo "<li><a href=\"./\">{$JlabTitle}へ戻る</a></li>\n";
}
?>
</ul>
<br style="clear:both">
</div>

<?php
//この先はログイン済みの場合に表示されます
if( $MegaEditor ){

	echo "<!-- ImageList -->\n";
	echo "<div id=\"ImageList\">\n\n";
	
	//ログファイル名
	$LogFileName = "./{$LogFolder}/ImageList.txt";
	
	//今日を表示
	if( $DisplayDay == "today" ){
		$SetDay = date("ymd");
	}
	
	//一覧表示
	else if(( $DisplayDay == "" )||( $DisplayDay == "list" )){
		$DisplayDay = "list";
	}
	
	//指定日を表示
	else{
		$SetDay = date("ymd", strtotime("- {$DisplayDay} days"));
	}
	
	//ページ未指定の場合は1ページ目を表示
	if( $CurrentPage == "" ){
		$CurrentPage = 1;
	}
	
	//日付ラベルを表示
	$DayLabel = "<div class=\"ImagePageLink\">\n";
	$DayLabel .= "<ul style=\"padding:0\">\n";
	
	//一覧のラベルを表示
	if(( $DisplayDay == "" )||( $DisplayDay == "list" )){
		$DayLabel .= "<li style=\"border-bottom:2px solid #ededed\">一覧</li>\n";
	}else{
		$DayLabel .= "<a href=\"./mega-editor.php\"><li>一覧</li></a>\n";
	}
	
	if( $DisplayDay == "today" ){
		$DayLabel .= "<li style=\"border-bottom:2px solid #ededed\">今日</li>\n";
	}else{
		$DayLabel .= "<a href=\"./mega-editor.php?Day=today\"><li>今日</li></a>\n";
	}
	
	for( $PRC = 1; $PRC <= $SaveDay; $PRC++ ){
		if( $DisplayDay == $PRC ){
			$DayLabel .= "<li style=\"border-bottom:2px solid #ededed\">{$PRC}日前</li>\n";
		}else{
			$DayLabel .= "<a href=\"?Day={$PRC}\"><li>{$PRC}日前</li></a>\n";
		}
	}
	
	$DayLabel .= "</ul>\n";
	$DayLabel .= "</div>\n\n";
	
	$ListIn = file_get_contents($LogFileName);
	$ImageList = explode("\n",$ListIn);
		
	//日付が指定されている場合は指定日のみ抽出する
	if(( $DisplayDay != "" )&&( $DisplayDay != "list" )){
		$ImageList = preg_grep("~^{$FileBaseName}{$SetDay}~",$ImageList);
		array_values($ImageList);
	}
	$ImageCount = count($ImageList);
	array_unshift($ImageList,"IMGLIST");

	
	$PageLabel = "<div class=\"ImagePageLink\">\n";
	$PageLabel .= "<ul style=\"padding:0\">\n";
	$PageCount = ceil($ImageCount/$DisplayImageCount);
	for( $PGC = 1; $PGC <= $PageCount; $PGC++ ){
		if( $CurrentPage == $PGC ){
			$PageLabel .= "<li style=\"border-bottom:2px solid #ededed\">{$PGC}</li>\n";
		}else{
			$PageLabel .= "<a href=\"?Day={$DisplayDay}&Page={$PGC}\"><li>{$PGC}</li></a>\n";
		}
	}
	$PageLabel .= "</ul>\n";
	$PageLabel .= "</div>\n\n";
	
	echo $DayLabel;
	
	for( $i = $DisplayImageCount*$CurrentPage-($DisplayImageCount-1); $i <= $DisplayImageCount*$CurrentPage; $i++ ){
	
		if( $ImageList[$i] == "" ){
			break;
		}
	
		$ListElement = explode("#",$ImageList[$i]);
		if( $ListElement[0] == "" ){
			continue;
		}
	
		//HTML出力
		echo "<div class=\"ImageElements\">\n";
		echo "<div>投稿日：{$ListElement[1]} ({$ListElement[2]}x{$ListElement[3]} : {$ListElement[4]}KB)</div>\n";
		echo "<a href=\"{$SaveFolder}/{$ListElement[0]}\" target=\"_blank\"><div class=\"InitImage\"><img src=\"{$ThumbSaveFolder}/{$ListElement[0]}\"></div></a>\n";
		echo "<div><input type=\"text\" class=\"TextBox\" style=\"width:350px\" onclick=\"this.select(0,this.value.length)\" value=\"{$FullURL}{$SaveFolder}/{$ListElement[0]}\" readonly></div>\n";
		echo "<div>\n";
		echo "<input type=\"button\" class=\"RedButton\" value=\"管理者権限で削除\" onclick=\"DeleteImage('{$ListElement[0]}')\"> ";
		echo "<input type=\"button\" class=\"BlueButton\" value=\"画像をダウンロード\" onclick=\"location.href='./mega-editor.php?EditMode=Dl&Arc={$ListElement[0]}'\"> ";
		echo "<input type=\"button\" class=\"BlueButton\" value=\"個別ログをダウンロード\" onclick=\"location.href='./mega-editor.php?EditMode=LogDl&Arc={$ListElement[0]}'\"><br>\n";
		echo "<input type=\"button\" style=\"margin-top:4px\" class=\"BlueButton\" value=\"個別ログを閲覧\" onclick=\"window.open('./mega-editor.php?EditMode=qLook&Arc={$ListElement[0]}')\">\n";
		echo "</div>\n";
		echo "<br style=\"clear:left;\">\n";
		echo "</div>\n\n";
		
	}
	
	//画像が存在しない場合はメッセージのみ
	if( $ImageCount == 0 ){
		echo "<div style=\"margin-left: 3em; padding: 2em 0;\">\n画像はアップロードされていません\n</div>\n\n";
	}else{
		
		$PrevLinkNum = $CurrentPage-1;
		$NextLinkNum = $CurrentPage+1;
		if( $PrevLinkNum != 0 ){
			$PrevLink = "<a class=\"ImagePageLinkLF\" href=\"?Day={$DisplayDay}&Page={$PrevLinkNum}\"><li style=\"width:70px\">Prev</li></a>\n";
		}else{
			$PrevLink = "";
		}
		if( $PageCount >= $NextLinkNum ){
			$NextLink = "<a class=\"ImagePageLinkLF\" href=\"?Day={$DisplayDay}&Page={$NextLinkNum}\"><li style=\"width:70px\">Next</li></a>\n";
		}else{
			$NextLink = "";
		}
		
		echo "<div class=\"ImagePageLink\">\n";
		echo "<ul style=\"padding:0\">\n";
		echo $PrevLink;
		echo $NextLink;
		echo "</ul>\n";
		echo "</div>\n\n";
		
		echo $PageLabel;
		
	}

}
?>
</div>
</div>

<!-- Footer -->
<footer>
<div style="margin:2em 3em;">
	<p><a href="https://github.com/kouki-kuriyama/jlab-script-plus/" target="_blank"><script type="text/javascript">document.write(VersionNumber);</script> / MegaEditor </a></p>
</div>
</footer>

</body>
</html>