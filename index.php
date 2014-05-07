<?php

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
	$FileBaseName = $SettingData[13];
	
	if( $FileBaseName == "" ){
		$FileBaseName = "";
	}
	
	if( $SettingData[14] == 1 ){
		$UseDragDrop = "true";
	}else{
		$UseDragDrop = "false";
	}
	
	//削除キーのCookieを読み込む
	$LocalDeleteKey = $_COOKIE["DelKey"];
	
	//upload.phpの更新による再リクエスト防止の為にセッションCookieを持たせる
	session_start();
	$_SESSION["JCK"] = "Ready";
	
	//日付とページを取得する
	$DisplayDay = $_GET["Day"];
	$CurrentPage = $_GET["Page"];
	
	//今日を表示
	if( $DisplayDay == "today" ){
		$MetaRobots = "noindex";
		$SetDay = date("ymd");
		$LogFileName = "./{$LogFolder}/ImageList-{$SetDay}.txt";
	}
	
	//一覧表示
	else if(( $DisplayDay == "" )||( $DisplayDay == "list" )){
		if( $DisplayDay == "" ){
			$MetaRobots = "index";
		}else{
			$MetaRobots = "noindex";
		}
		$DisplayDay = "list";
		$LogFileName = "./{$LogFolder}/ImageList-all.txt";
	}
	
	//指定日を表示
	else{
		$MetaRobots = "noindex";
		$SetDay = date("ymd", strtotime("- {$DisplayDay} days"));
		$LogFileName = "./{$LogFolder}/ImageList-{$SetDay}.txt";
	}
	
	if( $CurrentPage == "" ){
		$CurrentPage = 1;
	}else{
		$MetaRobots = "noindex";
	}

}else{
	$SettingData = false;
	echo "<div style=\"margin:2em 0 2em 3em; color:red; font-size:24px\">設定ファイルがありません！</div>\n\n";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="robots" content="<?php echo $MetaRobots; ?>">
<title><?php echo $JlabTitle; ?></title>

<!-- StyleSheet -->
<style type="text/css">

/* --- Main --- */
body {
	width:100%;
	margin:0;
	padding:0;
	padding-bottom:225px !important;
	background:#f5f5f5;
	color:#000;
	font-family:Helvetica,"Meiryo UI",sans-serif;
	font-size:14px;
	text-align:left;
}

img { border:0px; }

a { color:#444; }
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

/* --- Uploader --- */
#Uploader {
	position:relative;
	padding:2em 0 2em 3em;
	background:#fff;
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
}

#UploaderCurtain {
	width:100%;
	height:100%;
	text-align:center;
	display:none;
	position:absolute;
	top:0px;
	left:0px;
	background:#000;
	color:#fff;
	filter: alpha(opacity=70);
	-moz-opacity:0.7;
	opacity:0.7;
}

#JlabRing {
	width:550px;
	height:130px;
	position:absolute;
	top:0px;
	left:100%;
	margin-left:-550px;
}

#UploaderPanel {
	margin:10px 0;
}

#UploaderPanel div {
	width:120px;
	height:40px;
	line-height:40px;
	float:left;
}

#Preview img {
	max-width:<?php echo $MaxThumbWidth; ?>px;
	max-height:<?php echo $MaxThumbHeight; ?>px;
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

.ImageElements div {
	padding-bottom:10px;
}

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

/* --- URLBox --- */
#URLBox {
	width:100%;
	height:225px;
	margin-top:-45px;
	position:fixed;
	top:100%;
	left:0%;
	z-index:3;
	transition:0.5s cubic-bezier(0.23,1,0.32,1);
	-webkit-transition:0.5s cubic-bezier(0.23,1,0.32,1);
	-moz-transition:0.5s cubic-bezier(0.23,1,0.32,1);
}

#URLBox a {
	text-decoration:none;
}

#URLBoxLabel {
	width:150px;
	height:45px;
	margin-left:-180px;
	position:relative;
	left:100%;
	z-index:8;
	background:#fff;
	border:1px solid #ccc;
	border-bottom:0px;
	line-height:45px;
	text-align:center;
}

#URLBoxInner {
	width:100%;
	height:140px;
	padding:20px 2em;
	position:fixed;
	z-index:9;
	background:#fff;
	border-top:1px solid #ccc;
}

#LinkMenu {
	margin:2em 0 2em 3em;
}


</style>

<!-- Javascript -->
<script type="text/javascript" src="./DragDrop.js"></script>
<script type="text/javascript">
var OpenURLBox = false;
var UseDragDrop = <?php echo $UseDragDrop; ?>;

function urlbox( ub_cmd ){

	if( !OpenURLBox ){
		ToggleURLBox();
	}

	switch( ub_cmd ){
		case "clear":
			document.getElementById("urlbox-textarea").value = "";
		break;

		default:
			before_urlbox_textarea = document.getElementById("urlbox-textarea").value;
			document.getElementById("urlbox-textarea").value = ub_cmd + "\n" + before_urlbox_textarea;
		break;
	}

	return;
}

function ToggleURLBox(){

	if( !OpenURLBox ){
		document.getElementById("URLBox").style.marginTop = "-225px";
		document.getElementById("URLBoxInner").style.boxShadow = "0 0 10px #000";
		OpenURLBox = true;
	}else{
		document.getElementById("URLBox").style.marginTop = "-45px";
		document.getElementById("URLBoxInner").style.boxShadow = "0 0 0 #000";
		OpenURLBox = false;
	}

}

function ImageUploading(){
	document.getElementById("UploaderCurtain").style.display = "block";
	document.ImageUploader.submit();
}

window.onload = function(){
	CheckEnableFileAPI();
}
</script>

</head>
<body ondragover="onFileOver(event)" ondrop="onFileDrop(event)">

<!-- Header -->
<header>
<h1><?php echo $JlabTitle; ?></h1>
</header>

<!-- Contents -->
<div id="Contents">

<!-- Uploader -->
<div id="Uploader">

<!-- Curtain -->
<div id="UploaderCurtain">
<div style="margin-top:30px; font-size:18px;">アップロード中です...</div>
</div>

<!-- JlabRing : 実況ろだに参加する際は下のコメントアウトを除去して下さい -->
<!--
<iframe src="http://livech.sakura.ne.jp/jlab/ring.html" id="JlabRing" frameborder="no" scrolling="no"></iframe>
-->

<span id="UploaderMessage">画像をブラウザ上に<strong>ドラッグアンドドロップ</strong>するか、ファイルを選択してください</span>
<form method="post" enctype="multipart/form-data" id="UploaderPanel" name="ImageUploader" action="upload.php">
	<p id="Preview"></p>
	<div style="font-weight:bold">ファイル</div>
	<div style="width:400px !important;"><input type="file" name="Image" id="UploadMedia"><span id="LoadedFileName"></span></div>
	<div style="display:none"><input type="hidden" name="ImageBase64" id="ImageBase64N"></div>
	<br style="clear:both">
	<div style="font-weight:bold">削除キー</div>
	<div><input type="password" id="DeleteKeyBox" name="DeleteKey" value="<?php echo $LocalDeleteKey; ?>" class="TextBox"></div>
	<br style="clear:both">
	<div style="width:400px"><input type="button" class="BlueButton" value="アップロード" onclick="ImageUploading()"> <input type="button" class="RedButton" value="リセット" onclick="AllClear()"></div>
	<br style="clear:both">
</form>

<ul style="list-style:none; padding:0; margin:0">
	<li>JPG GIF PNG / MAX <span style="font-size:18px"><?php echo $MaxSize; ?></span>KB / <span style="font-size:20px"><?php echo $SaveDay; ?></span>日間保存 / Admin <?php echo $Admin; ?></li>
	<li>連投可能 / URL [<?php echo "{$FullURL}{$SaveFolder}/{$FileBaseName}"; ?>+number.ext]</li>
</ul>
	
<p>
<?php echo $JlabTitle; ?> は画像アップロード後<?php echo $SaveDay; ?>日間画像を保存し、<?php echo $SaveDay; ?>日間を過ぎると画像は自動的に削除されます。<br>
全体の投稿数により削除される仕組みでは無いため、確実に<?php echo $SaveDay; ?>日間の間は閲覧できる状態にしたい場合や長い間保存しておきたくない画像などの公開に向いています。
</p>
</div>

<!-- LinkMenu -->
<div id="LinkMenu">
</div>

<!-- ImageList -->
<div id="ImageList">
<?php

$DayLabel = "<div class=\"ImagePageLink\">\n";
$DayLabel .= "<ul style=\"padding:0\">\n";

//一覧のラベルを表示
if(( $DisplayDay == "" )||( $DisplayDay == "list" )){
	$DayLabel .= "<li style=\"border-bottom:2px solid #ededed\">一覧</li>\n";
}else{
	$DayLabel .= "<a href=\"./\"><li>一覧</li></a>\n";
}

if( $DisplayDay == "today" ){
	$DayLabel .= "<li style=\"border-bottom:2px solid #ededed\">今日</li>\n";
}else{
	$DayLabel .= "<a href=\"./?Day=today\"><li>今日</li></a>\n";
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

if( file_exists($LogFileName) ){

	$ListIn = file_get_contents($LogFileName);
	$ImageList = explode("\n",$ListIn);
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
		echo "<div><input type=\"button\" class=\"BlueButton\" onclick=\"urlbox('{$FullURL}{$SaveFolder}/{$ListElement[0]}')\" value=\"Add URL\"> ";
		echo "<input type=\"button\" class=\"RedButton\" value=\"Delete\" onclick=\"location.href='./delete.php?Arc={$ListElement[0]}'\"></div>\n";
		echo "<br style=\"clear:left;\">\n";
		echo "</div>\n\n";

	}
	
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


}else{

	echo $DayLabel;
	echo "<div style=\"margin-left: 3em; padding: 2em 0;\">\n画像はアップロードされていません\n</div>\n\n";

}
?>
</div>
</div>

<!-- Footer -->
<footer>
<div style="margin:2em 3em; ">
	<p><a href="https://github.com/kouki-kuriyama/jlab-script-plus/" target="_blank">jlab-script-plus Ver0.03e</a>｜<a href="./mega-editor.php">管理者用メガエディター</a></p>
</div>
</footer>

<!-- URLBox -->
<div id="URLBox">
<div id="URLBoxLabel"><a href="javascript:void(0)" onclick="ToggleURLBox()"><div>URLBox</div></a></div>
	<div id="URLBoxInner">
	<textarea id="urlbox-textarea" class="TextBox" style="width:60%; height:80px; margin-bottom:10px"></textarea><br>
	<input type="button" class="BlueButton" value="Clear" onclick="urlbox('clear')">
	</div>
</div>

</body>
</html>

	