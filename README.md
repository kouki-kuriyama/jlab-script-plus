jlab-script-plus Ver0.06
================
  
**2015年9月30日 jlab-script-plus Ver0.06 を公開しました**  
  
jlab-script-plus は保存期間を設定できるアップローダースクリプトです。  
全体の投稿数により削除される仕組みでは無いため、確実に設定した間は閲覧できる状態にしたい場合や長い間保存しておきたくない画像などの公開に向いています。  
このスクリプトは **JLAB - 実況ろだ 明るい分散計画** 向けに作成されたスクリプトですが、それ以外の場所でもアップローダーとして設置していただけます。

##このバージョン以降ついて
jlab-script-plus Ver0.06 よりパスワードハッシュアルゴリズムの変更やフォルダ名変更など、大幅な変更を行っております。  
Ver0.06 dev3以前(Ver0.05a等のmasterバージョンも含む)のバージョンからアップデートを行う際は s フォルダ(画像保存フォルダ)、t フォルダ(サムネイル保存フォルダ)、dフォルダ(ログ保存フォルダ)以外のファイル・フォルダをすべて削除してから、新しいバージョンのjlab-script-plusを設定してください。  
また、Ver0.06 dev3以前にアップロードされた画像は、アルゴリズム変更に伴いこのバージョンではパスワードを入力しての削除が行えません。ご注意ください。  

##必要環境
* PHP 5.3以上推奨
 - 全ての機能を利用するには PHP 5.4 以上推奨です
* GDライブラリ

##ダウンロード
右下の **Download ZIP** からスクリプト一式をダウンロードします。

##ファイル・フォルダ一覧
初期設定では以下の通りにファイル、フォルダを設置することにより動作します。  
[707]は推奨パーミッションを表しています。（サーバにより推奨の設定は異なります。）  
settings.phpで簡易的なパーミッションの確認を行います。  

* /s [707]
* /t [707]
* /d [707]
* /static [707]
 - Thumb.php
 - jlab-script-plus.css
 - jlab-script-plus.js
* index.php
* upload.php
* delete.php
* mega-editor.php
* functions.php
* settings.php
* custom-html.php
* .htaccess

##設置方法
1.settings.php を開き、各項目を設定します。  
2.すべてのファイル・フォルダをアップロードします。  
3.ブラウザから settings.php にアクセスし、staticフォルダに setting.dat と process.dat が作成されたことを確認します。  
4.トップページにアクセスし、画像が正しくアップロードできるか確認します。  

##メガエディター
メガエディターは Ver0.03b から同梱されているファイルです。  
ブラウザ上から mega-editor.php にアクセスし、設定したマスターキーでログインをすると、以下の操作がメガエディター上で簡単に行えます。  
メガエディターは現在開発中の為、今後のアップデートで機能が追加及び変更になる場合があります。  

* 画像のダウンロード
* 画像の個別ログをダウンロード
* 画像を管理者権限で削除
* 画像の個別ログの簡易閲覧(IPアドレスや削除キー等の情報)
* 破損及び紛失したログファイルのリストア
* 保存期間の過ぎた画像を一括削除

##ドラッグアンドドロップによる画像の取り込み
ドラッグアンドドロップによる画像の取り込み機能は Ver0.03c より追加されました。  
画像をアップロードする際に、ファイル選択ダイアログを使用せず、ブラウザ画面に画像をドラッグアンドドロップするだけで画像がアップロードできる機能です。  
この機能はサーバ側とブラウザ側が以下の条件を満たしている場合のみ使用できます。  
以下の条件を満たしていない場合は、従来のアップローダーが表示されます。

* サーバ側のPHPが 5.4.0 以上
* ブラウザがHTML5 FileAPIに対応(最新のChrome/Firefox/SafariやIE10以降)

##カスタムHTMLの設定
ろだ独自のカスタムHTMLやメニューを custom-html.php に記入しておくと、スクリプトのバージョンアップを行う際に index.php を書き換える作業が不要になります。  
custom-html.php で設定したカスタムHTMLがどの部分に挿入されるかは index.php をご確認ください。

##再配布等について
再配布・転載・フォークはご自由にしていただいて構いません。  
バグ修正版の公開等はこのGitHubでのみ行いますので、アップローダー下部にあるGitHubへのリンクは削除されないことをおすすめします。

##更新履歴
* **Ver0.06** (2015/09/30)
 - URLBoxのURLをボタンクリックでクリップボードにコピーできる機能を追加（IE/Chrome）
 - URLBoxのURLを自動で保存する機能を追加
 - upload.php のコードを見直し、23%のコードを削減
 - static-data フォルダを static フォルダに改称
 - アップロード完了画面で続けて画像をアップロードできるように変更
 - よく使う処理を functions.php に関数化
 - 全てのスクリプトファイルを新仕様に変更
 - マスターキーを settings.php に記入する仕様へ変更
 - 動作に必要なPHPバージョンを 5.3 に引き下げ
 - パスワードハッシュアルゴリズムを SHA-512 に変更
 - 一部サーバーでドラッグアンドドロップの設定が正しく出来ない不具合を修正
 - 期限切れ画像一覧削除に発生していた不具合を修正（メガエディター）
 - サムネイル再生成時にファイル名が正しく設定されない不具合を修正（メガエディター）
 - パスワードを平文で保存するお助け仕様を廃止
 - CronJobによる画像削除機能を廃止
 - 日付で個別にログを生成する仕様を廃止 
* **Ver0.05a** (2014/08/27)
 - 64ビットOSの一部環境でメモリーリークが発生する不具合を修正
 - ドラッグアンドドロップによるアップロードの信頼性を向上
 - アップロード前の警告表示機能を追加
* **Ver0.04b** (2014/06/15)
 - アップロードページに保存期間を表示するように変更
 - upd-manage.dat を設定時に自動生成するように変更
 - settings.php でフォルダのパーミッションを確認する機能を追加
 - メガエディターに保存期間が超過した画像を一括削除できる機能を追加
* **Ver0.04a** (2014/05/27)
 - 重複アップロード時に異常が発生する問題を修正
 - CSSを jlab-script-plus.css に統一
 - Javascriptを jlab-script-plus.js に統一
 - 画像をドラッグアンドドロップで取り込んだ時にメッセージを表示するように変更
 - 画像配信URLを変更する機能を追加
 - カスタムHTML機能を追加
* **Ver0.03e** (2014/05/08)
 - アップロード時にJavascriptを使用するように変更(一部荒らし対策)
 - アップロード時に「アップロード中...」の表示を追加
 - JLABリングのiframe表示を修正
* **Ver0.03d** (2014/04/18)
 - メガエディターのバグを修正
 - アップロード時の不具合を修正
 - 一覧表示を追加
 - Next/Prevリンクを追加
* **Ver0.03c** (2014/04/01)
 - ドラッグアンドドロップによる画像の取り込み機能を追加
 - メガエディターのバグを修正
 - アップロード完了後のバグを修正
 - 推奨PHPのバージョンを 5.2 から 5.4 へ引き上げ
* **Ver0.03b** (2014/03/26)
 - ろだ管理に便利なメガエディター(mega-editor.php)を同梱
 - ファイル名に接頭語を設定できるオプションを追加
* **Ver0.03a** (2014/03/18)
 - 削除キーの暗号化に関するオプションを追加
 - 細かいバグ修正
 - セキュリティ面を強化する為、.htaccess を標準で同梱
* **Ver0.02b** (2014/03/10)
 - 一部ブラウザでアップロード出来ない問題を修正
 - セキュリティ面を強化
* **Ver0.02a** (2014/03/09)
 - マニュアル削除機能を有効
 - バグ修正
* **Ver0.01b** (2014/02/19)
 - 保存期間の指定ができるように変更
 - デフォルト暗号化アルゴリズムを変更
 - index.phpのフルパス表示の不具合を修正
* **Ver0.01a** (2014/02/12)
 - リリース

##動作確認環境
* Apache 2.4
* CentOS 6 x86_64
* PHP 5.5.10 ( +gd +mcrypt )

##開発環境
* Mac OS X 10.10
* mi (テキストエディタ)