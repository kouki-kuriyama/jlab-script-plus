<?php

//保険
$MegaEditor = false;

//マスターキーを読み込む
require_once("./masterkey.php");

//引数取得
$DisplayDay = $_GET["Day"];
$CurrentPage = $_GET["Page"];

//設定ファイルの読み込みをする
if( file_exists("./static-data/setting.dat") ){
	$SettingsData = file_get_contents("./static-data/setting.dat");
	$SettingData = explode("\n",$SettingsData);
	
	$JlabTitle = $SettingData[0];
	$Admin = $SettingData[1];
	$SaveFolder = $SettingData[2];
	$ThumbSaveFolder = $SettingData[3];
	$LogFolder = $SettingData[4];
	$FullURL = $SettingData[5];
	$MaxSize = (int)$SettingData[6] / 1024;
	$MaxThumbWidth = $SettingData[7];
	$MaxThumbHeight = $SettingData[8];
	$DisplayImageCount = $SettingData[9];
	$SaveDay = $SettingData[10];
	
}else{
	$SettingData = false;
	echo "<div style=\"margin:2em 0 2em 3em; color:red; font-size:24px\">設定ファイルがありません</div>\n\n";
}

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
		$MainSetHTML .= "アップローダーの設定・マスターキーの変更はファイル本体を編集する必要があります。<br>\n";
		$MainSetHTML .= "<span style=\"font-size:12px; color:#666\">今後のバージョンアップでメガエディターからアップローダーの設定が変更できるようになる予定です。</span>\n";
	
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
	$MainSetHTML .= "<span style=\"font-size:12px; color:#666\">今後のバージョンアップでメガエディターからアップローダーの設定が変更できるようになる予定です。</span>\n";
	
	//すべてのクエリを読み込む
	$ArcData = $_GET["Arc"];
	$EditMode = $_GET["EditMode"];
	
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
	
	//モード処理(画像のダウンロード)
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
	
	//モード処理(画像削除)
	else if(( $EditMode == "Del" )&&( !empty($ArcData) )){
	
		//ファイルが存在するかを確認
		if( file_exists("./{$SaveFolder}/{$ArcData}") ){
			
			//拡張子とファイル名を分割
			list($RFileName,$ExtensionID) = explode(".",$ArcData);
			
			//アップロード日を取得する
			$UploadedDate = substr($RFileName,0,6);
			
			//管理者権限で削除
			unlink("./{$SaveFolder}/{$ArcData}");
			unlink("./{$ThumbSaveFolder}/{$ArcData}");
			unlink("./{$LogFolder}/{$RFileName}.dat");
			
			//一覧から削除する
			$ImageList = file_get_contents("./{$LogFolder}/ImageList-{$UploadedDate}.txt");
			$ImageList = explode("\n",$ImageList);
			foreach($ImageList as $key => $value) {
				if( preg_match("~{$ArcData}~",$value) ) {
					break;
				}
			}
			unset($ImageList[$key]);
			file_put_contents("./{$LogFolder}/ImageList-{$UploadedDate}.txt",implode("\n",$ImageList));
			
			//メッセージ
			$MainSetHTML = "<span style=\"font-weight:bold; color:blue\">{$ArcData} は削除されました</span>\n";

		}else{
			//メッセージ
			$MainSetHTML = "<span style=\"font-weight:bold; color:red\">{$ArcData} は見つかりませんでした</span>\n";
		}
	}
	
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
	
	//イメージリストをクイックルック
	else if(( $EditMode == "qLookList" )&&( $ArcData != "" )){
	
		//表示する日付を取得
		$GetLogListDay = date("ymd", strtotime("- {$ArcData} days"));
	
		//ファイルが存在するかを確認
		if( file_exists("./{$LogFolder}/ImageList-{$GetLogListDay}.txt") ){
			header("Content-type: text/plain");
			readfile("./{$LogFolder}/ImageList-{$GetLogListDay}.txt");
			exit;
		}else{
			header("Content-type: text/plain");
			echo "この日のログはありません";
			exit;
		}
	}
	
	//ログを再生成する
	else if(( $EditMode == "RestoreLog" )&&( $ArcData != "" )){
	
		//リストアする日付を取得
		$GetRestoreDay = date("ymd", strtotime("- {$ArcData} days"));
		
		//ログファイル名
		$LogFileName = "./{$LogFolder}/ImageList-{$GetRestoreDay}.txt";
		
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
				
					//再生成する日付に一致するファイルをGETする
					if( preg_match("~^(.*){$GetRestoreDay}~",$NDFileName) ){
					
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
						
						$FileNamelist[] = $NDFileName."#{$UpYear}/{$UpMonth}/{$UpDay} {$UpHour}:{$UpDay}:{$UpSecond}#{$ImageWidth}#{$ImageHeight}#{$FileSizes}";
					
					}
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
	
	//メガエディターからログアウト
	else if( $EditMode == "Logout" ){
		$MegaEditor = false;
		setcookie("MEditor","Login", time()-3600);
		header("Location:./");
		exit;
	}
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title>メガエディター : <?php echo $JlabTitle; ?></title>

<!-- StyleSheet -->
<style type="text/css">

/* --- Main --- */
body {
	width:100%;
	margin:0;
	padding:0;
	background:#f5f5f5;
	color:#000;
	font-family:Helvetica,"Meiryo UI",sans-serif;
	font-size:14px;
	text-align:left;
}

img { border:0px; }

a { color:#444;  text-decoration:underline; }
a:hover { text-decoration:none; }

input { margin:0 }

h1 {
	margin:0;
	padding:1em 2em;
	color:#444;
	font-size:24px;
}

/* --- Input --- */
.TextBox {
	height:24px;
	padding:3px;
	background:#ffffff;
	border:2px solid #9c9c9c;
	border-radius:0px;
	outline:none;
	transition:0.5s ease;
	-webkit-transition:0.5s ease;
	-moz-transition:0.5s ease;
}

.TextBox:hover { box-shadow:0 0 7px #9c9c9c; }

.BlueButton,.RedButton {
	width:150px;
	height:30px;
	outline:none;
	border:0px;
	border-radius:0px;
	color:#fff;
	text-shadow:0 0 5px #fff;
	transition:0.5s ease;
	-webkit-transition:0.5s ease;
	-moz-transition:0.5s ease;
}
.BlueButton { background:#004ab2; }
.BlueButton:hover { box-shadow:0 0 7px #004ab2; }
.BlueButton:active { box-shadow:0 0 0 #004ab2; }

.RedButton { background:#ff4f4f; }
.RedButton:hover { box-shadow:0 0 7px #ff4f4f; }
.RedButton:active { box-shadow:0 0 0 #ff4f4f; }

/* --- MainSet --- */
#MainSet {
	position:relative;
	padding:2em 0 2em 3em;
	background:#fff;
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
}

#MainSetPanel {
	margin:10px 0;
}

/* --- List --- */
#ImageList {
	background:#fff;
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
}

#ImageList ul {
	margin-left:3em !important;
}

.ImagePageLink li {
	margin-right:2.5px;
	padding:5px;
	border-bottom:2px solid #999;
	list-style:none;
	display:inline-block;
}

.ImagePageLink li:hover {
	border-bottom:2px solid #ededed;
}

.ImagePageLink a {
	color:#000;
	text-decoration:none;
}

.ImageElements {
	margin-left:3em;
	padding:2em 0;
	border-bottom:1px solid #ccc;
}

.ImageElements img {
	float:left;
	margin-right:10px;
	max-width:<?php echo $MaxThumbWidth; ?>px;
	max-height:<?php echo $MaxThumbHeight; ?>px;
}

.ImageElements div {
	padding-bottom:10px;
}

/* --- LinkMenu --- */
#LinkMenu {
	margin:2em 0 2em 3em;
	padding:0px;
	list-style:none;
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
function RestoreLog(RestoreDay){

	if(( RestoreDay == "0" )||( RestoreDay == "" )){
		ResDisDay = "本日";
		RestoreDay = 0;
	}else{
		ResDisDay = RestoreDay + "日前";
	}

	if( window.confirm(ResDisDay + "のログをリストアします。\n(※ImageList.txtのログを再生成し、個々のログはリストアしません)") ){
		location.href="./mega-editor.php?EditMode=RestoreLog&Arc=" + RestoreDay + "";
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
<div id="MainSet">
<?php echo $MainSetHTML; ?>
</div>

<!-- LinkMenu -->
<ul id="LinkMenu">
<?php
if( $MegaEditor ){
	echo "<li><a href=\"mega-editor.php?EditMode=qLookList&Arc={$DisplayDay}\" target=\"_blank\">この日のログをクイックルック</a></li>\n";
	echo "<li><a href=\"mega-editor.php\" onclick=\"RestoreLog('{$DisplayDay}'); return false;\">この日のログをリストア</a></li>\n";
	echo "<li><a href=\"?EditMode=Logout\">ログアウト</a></li>\n";
}else{
	echo "<li><a href=\"./\">{$JlabTitle}へ戻る</a></li>\n";
}
?>
<br style="clear:both">
</ul>

<?php
//この先はログイン済みの場合に表示されます
if( $MegaEditor ){

	echo "<!-- ImageList -->\n";
	echo "<div id=\"ImageList\">\n\n";
	
	if(( $DisplayDay == "" )||( $DisplayDay == 0 )){
		$SetDay = date("ymd");
		$DisplayDay = 0;
	}else{
		$SetDay = date("ymd", strtotime("- {$DisplayDay} days"));
	}
	
	if( $CurrentPage == "" ){
		$CurrentPage = 1;
	}
	
	$DayLabel .= "<div class=\"ImagePageLink\">\n";
	$DayLabel .= "<ul style=\"padding:0\">\n";
	for( $PRC = 0; $PRC <= $SaveDay; $PRC++ ){
		if( $DisplayDay == $PRC ){
			$DayLabel .= "<li style=\"border-bottom:2px solid #ededed\">{$PRC}日前</li>\n";
		}else{
			$DayLabel .= "<a href=\"?Day={$PRC}\"><li>{$PRC}日前</li></a>\n";
		}
	}
	$DayLabel .= "</ul>\n";
	$DayLabel .= "</div>\n\n";
	$DayLabel = str_replace("0日前", "今日", $DayLabel);
	
	if( file_exists("./{$LogFolder}/ImageList-{$SetDay}.txt") ){
	
		$ListIn = file_get_contents("./{$LogFolder}/ImageList-{$SetDay}.txt");
		$ImageList = explode("\n",$ListIn);
		$ImageCount = count($ImageList);
		array_unshift($ImageList,"IMGLIST");
	
		$PageLabel .= "<div class=\"ImagePageLink\">\n";
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
			echo "<a href=\"{$SaveFolder}/{$ListElement[0]}\" target=\"_blank\"><img src=\"{$ThumbSaveFolder}/{$ListElement[0]}\"></a>\n";
			echo "<div id=\"InitArea{$i}\"><input type=\"text\" class=\"TextBox\" style=\"width:350px\" onclick=\"this.select(0,this.value.length)\" value=\"{$FullURL}{$SaveFolder}/{$ListElement[0]}\" readonly></div>\n";
			echo "<div id=\"InitButtonArea{$i}\">\n";
			echo "<input type=\"button\" class=\"RedButton\" value=\"Delete\" onclick=\"DeleteImage('{$ListElement[0]}')\"> ";
			echo "<input type=\"button\" class=\"BlueButton\" value=\"Download Image\" onclick=\"location.href='./mega-editor.php?EditMode=Dl&Arc={$ListElement[0]}'\"> ";
			echo "<input type=\"button\" class=\"BlueButton\" value=\"Download Log(.txt)\" onclick=\"location.href='./mega-editor.php?EditMode=LogDl&Arc={$ListElement[0]}'\"><br>\n";
			echo "<input type=\"button\" style=\"margin-top:4px\" class=\"BlueButton\" value=\"QuickLook Log\" onclick=\"window.open('./mega-editor.php?EditMode=qLook&Arc={$ListElement[0]}')\">\n";
			echo "</div>\n";
			echo "<br style=\"clear:left;\">\n";
			echo "</div>\n\n";
	
		}
		
		echo $PageLabel;
	
	}else{
	
		echo $DayLabel;
		echo "<div style=\"margin-left: 3em; padding: 2em 0;\">\n画像はアップロードされていません<br>\n画像がアップロードされるとログが生成されます</div>\n\n";
	
	}

}
?>
</div>
</div>

<!-- Footer -->
<footer>
<div style="margin:2em 3em;">
	<p><a href="https://github.com/kouki-kuriyama/jlab-script-plus/" target="_blank">jlab-script-plus Ver0.03b</a></p>
</div>
</footer>

</body>
</html>