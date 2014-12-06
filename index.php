<?php

//カスタムHTMLの読み込み
require_once("./custom-html.php");

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
	$TransportURL = $SettingData[15];
	
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
	
	//ログファイルのパス
	$LogFileName = "./{$LogFolder}/ImageList.txt";
	
	//今日を表示
	if( $DisplayDay == "today" ){
		$MetaRobots = "noindex";
		$SetDay = date("ymd");
	}
	
	//一覧表示
	else if(( $DisplayDay == "" )||( $DisplayDay == "list" )){
		if( $DisplayDay == "" ){
			$MetaRobots = "index";
		}else{
			$MetaRobots = "noindex";
		}
		$DisplayDay = "list";
	}
	
	//指定日を表示
	else{
		$MetaRobots = "noindex";
		$SetDay = date("ymd", strtotime("- {$DisplayDay} days"));
	}
	
	//トップページ以外は検索結果に表示しない
	if( $CurrentPage == "" ){
		$CurrentPage = 1;
	}else{
		$MetaRobots = "noindex";
	}

}else{
	$SettingData = false;
	echo "［エラー］設定ファイルがありません。<br>\n";
	echo "　　　　　スクリプトを開始するには、アップローダーの設定をする必要があります。";
	exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="robots" content="<?php echo $MetaRobots; ?>">
<title><?php echo $JlabTitle; ?></title>

<!-- Default CSS/Javascript -->
<link type="text/css" rel="stylesheet" href="./static-data/jlab-script-plus.css">
<script type="text/javascript" src="./static-data/jlab-script-plus.js"></script>

<!-- CSS -->
<style type="text/css">
#Preview img {
	max-width:<?php echo $MaxThumbWidth; ?>px;
	max-height:<?php echo $MaxThumbHeight; ?>px;
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
</style>

<!-- Javascript -->
<script type="text/javascript">
var OpenURLBox = false;
var UseDragDrop = <?php echo $UseDragDrop; ?>;

//ドラッグアンドドロップチェック
window.onload = function(){
	CheckEnableFileAPI();
}
</script>

</head>
<body ondragover="onFileOver(event)" ondrop="onFileDrop(event)">

<!-- DragDropCurtain -->
<div id="DragDropCurtain">
<div style="margin:2em 3em" id="DragDropCurtainT">ドロップして画像を取り込みます</div>
</div>

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
			<div style="display:none"><input type="hidden" name="type" value="dialog"></div>
			<br style="clear:both">
			<div style="font-weight:bold">削除キー</div>
			<div><input type="password" id="DeleteKeyBox" name="DeleteKey" value="<?php echo $LocalDeleteKey; ?>" class="TextBox"></div>
			<br style="clear:both">
			<div style="font-weight:bold">保存期間</div>
			<div style="width:400px !important;"><?php echo date("Y年n月j日")."〜".date("Y年n月j日",strtotime("+ {$SaveDay} days")); ?></div>
			<br style="clear:both">
			<div style="width:400px"><input type="button" class="BlueButton" value="アップロード" onclick="ImageUploading()"> <input type="button" class="RedButton" value="リセット" onclick="AllClear()"></div>
			<br style="clear:both">
		</form>
	
		<ul style="list-style:none; padding:0; margin:0">
			<li>JPG GIF PNG / MAX <span style="font-size:18px"><?php echo $MaxSize; ?></span>KB / <span style="font-size:20px"><?php echo $SaveDay; ?></span>日間保存 / Admin <?php echo $Admin; ?></li>
			<li>連投可能 / URL [<?php echo "{$TransportURL}{$FileBaseName}"; ?>+number.ext]</li>
		</ul>
		
		<!-- CustomHTML 2 -->
		<div style="margin:1em 0">
		<?php echo $CustomHTML2; ?>
		</div>
		
	</div>
	
<!-- LinkMenu(CustomHTML 1) -->
<div id="LinkMenu">
<?php echo $CustomHTML1; ?>
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

	//画像が終わった場合は終了する
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
	echo "<a href=\"{$TransportURL}{$ListElement[0]}\" target=\"_blank\"><div class=\"InitImage\"><img src=\"{$ThumbSaveFolder}/{$ListElement[0]}\"></div></a>\n";
	echo "<div><input type=\"text\" class=\"TextBox\" style=\"width:350px\" onclick=\"this.select(0,this.value.length)\" value=\"{$TransportURL}{$ListElement[0]}\" readonly></div>\n";
	echo "<div><input type=\"button\" class=\"BlueButton\" onclick=\"urlbox('{$TransportURL}{$ListElement[0]}')\" value=\"Add URL\"> ";
	echo "<input type=\"button\" class=\"RedButton\" value=\"Delete\" onclick=\"location.href='./delete.php?Arc={$ListElement[0]}'\"></div>\n";
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
?>
</div>
</div>

<!-- Footer -->
<footer>
<div style="margin:2em 3em; ">
	<p><a href="https://github.com/kouki-kuriyama/jlab-script-plus/" target="_blank"><script type="text/javascript">document.write(VersionNumber);</script></a>｜<a href="./mega-editor.php">管理者用メガエディター</a></p>
</div>
</footer>

<!-- CustomHTML 3 -->
<div style="margin:2em 3em">
<?php echo $CustomHTML3; ?>
</div>

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

	