<?php

/*
	
	jlab-script-plus custom-html.php
	Version 0.07b / Kouki Kuriyama
	https://github.com/kouki-kuriyama/jlab-script-plus
	
	index.phpに設定するカスタムHTML用のファイルです。ろだ毎のメニュー作成や追記事項記入にお使いください
	スクリプトをバージョンアップする度に index.php を編集する必要がなくなります。(このcustom-html.phpの上書きに注意)
	カスタムHTMLの設定される部分は index.php をご確認ください。
	HTML・CSS・Javascriptを記入することができます。
*/

//メニュー用HTML
//アップロードパネル下に横メニューが表示されます
//コメントアウトを除去・サンプルを編集してください
$CustomHTML1 = <<<CustomHTML1
<!--
<ul style="padding:0; list-style:none;">
	<li style="float:left; margin-right:10px;"><a href="#">メニュー１</a></li>
	<li style="float:left; margin-right:10px;"><a href="#">メニュー２</a></li>
	<li style="float:left; margin-right:10px;"><a href="#">メニュー３</a></li>
</ul>
<br style="clear:both;">
-->
CustomHTML1;

//カスタムHTML 2
//アップローダーパネルの下部に表示されます
$CustomHTML2 = <<<CustomHTML2

このアップローダーはjlab-script-plusの最新版テストページです。<br>
（この部分のテキストは custom-html.php に書き込むことにより表示されます）

CustomHTML2;

//カスタムHTML 3
//画面下部(クレジット下部)に表示されます
$CustomHTML3 = <<<CustomHTML3

<!-- この部分にHTML -->

CustomHTML3;
?>