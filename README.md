jlab-script-plus Ver0.06 dev3
================
  
**2015年7月9日 jlab-script-plus Ver0.06 dev3 を公開しました**  
  
**このバージョンはデベロッパー版の開発版です。**
  
jlab-script-plus は保存期間を設定できるアップローダースクリプトです。  
全体の投稿数により削除される仕組みでは無いため、確実に設定した間は閲覧できる状態にしたい場合や長い間保存しておきたくない画像などの公開に向いています。  
このスクリプトは **JLAB - 実況ろだ 明るい分散計画** 向けに作成されたスクリプトですが、それ以外の場所でもアップローダーとして設置していただけます。

##必要環境
* PHP 5.4以上推奨
* GDライブラリ
* php-mcrypt(設定によっては不要)

##ダウンロード
右下の **Download ZIP** からスクリプト一式をダウンロードします。

##ファイル・フォルダ一覧
初期設定では以下の通りにファイル、フォルダを設置することにより動作します。  
[707]は推奨パーミッションを表しています。（サーバにより推奨の設定は異なります。）  
settings.phpで簡易的なパーミッションの確認を行います。  

* /s [707]
* /t [707]
* /d [707]
* /static-data [707]
 - Encryption.php
 - Thumb.php
 - jlab-script-plus.css
 - jlab-script-plus.js
* index.php
* upload.php
* delete.php
* masterkey.php
* mega-editor.php
* reg-delete.php
* settings.php
* custom-html.php
* .htaccess

##設置方法
1.settings.php を開き、各項目を設定します。  
2.masterkey.php を開き、settings.phpで設定したマスターキーを設定します。  
3.すべてのファイル・フォルダをアップロードします。  
4.ブラウザから settings.php にアクセスし、static-dataフォルダに setting.dat が作成されたことを確認します。  
5.トップページにアクセスし、画像が正しくアップロードできるか確認します。  
6.CronJobで reg-delete.php に１日１回アクセスするように設定します。(マニュアル削除の場合は不要)  
(※バージョンアップの場合は下の [バージョンアップ方法](https://github.com/kouki-kuriyama/jlab-script-plus#%E3%83%90%E3%83%BC%E3%82%B8%E3%83%A7%E3%83%B3%E3%82%A2%E3%83%83%E3%83%97%E6%96%B9%E6%B3%95) をご覧ください。)  
(※Ver0.05以前からのバージョンアップの場合は [バージョンアップ方法(Ver0.05からVer0.06へバージョンアップ)](https://github.com/kouki-kuriyama/jlab-script-plus#%e3%83%90%e3%83%bc%e3%82%b8%e3%83%a7%e3%83%b3%e3%82%a2%e3%83%83%e3%83%97%e6%96%b9%e6%b3%95%28Ver0%2e05%e3%81%8b%e3%82%89Ver0%2e06%e3%81%b8%e3%83%90%e3%83%bc%e3%82%b8%e3%83%a7%e3%83%b3%e3%82%a2%e3%83%83%e3%83%97%29) をご覧ください。)  

##CronJobの設定
CronJobの時間は午前0時5分頃がおすすめです。  
CronJobの設定できないサーバの場合は、外部から reg-delete.php のアクセスでも動作させることができますので、定期的にブラウザからアクセスするか、GAE等CronJobの設定ができるサーバから reg-delete.php にアクセスすることで同様の動作をさせることができます。
また、CronJob等の定期的なアクセスの設定ができない場合は下記の **マニュアル削除機能** をご覧ください。

##マニュアル削除機能
マニュアル削除機能は Ver0.02a から追加された機能です。  
日付が変わって最初の一枚目がアップロードされるのをトリガーとし、保存期間を超えた画像・サムネイル・ログファイルを削除する機能です。
CronJob等の定期的なアクセスを設定する必要が無いため、簡単に保存期間制限を設けることができます。  
マニュアル削除機能はデフォルトで有効になっています。reg-delete.php による削除をする場合には settings.php でマニュアル削除機能を無効にします。

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

##バージョンアップ方法(Ver0.05からVer0.06へバージョンアップ)
1.**Download ZIP**からスクリプト一式をダウンロードします。  
2.サーバ上の static-data/setting.dat を削除します。  
3.settings.php を開き、各項目を設定します。  
4.masterkey.php と custom-html.php(設定している場合) 以外のスクリプトをすべて上書きします。  
5.ログフォルダにあるtxtファイル(ImageList-[日付].txtとImageList-all.txt)を削除します。  
6.メガエディターにログインして、ログをリストアします。ログをリストアするとログフォルダに ImageList.txt という新しいログファイルが生成されます。

##バージョンアップ方法
1.**Download ZIP**からスクリプト一式をダウンロードします。  
2.サーバ上の static-data/setting.dat を削除します。  
3.settings.php を開き、各項目を設定します。  
4.masterkey.php と custom-html.php(設定している場合) 以外のスクリプトをすべて上書きします。  
(※画像やログファイルを削除せずにバージョンアップが可能です)

##再配布等について
再配布・転載・フォークはご自由にしていただいて構いません。  
バグ修正版の公開等はこのGitHubでのみ行いますので、アップローダー下部にあるGitHubへのリンクは削除されないことをおすすめします。

##更新履歴
  
**developブランチの更新履歴は develop-history.md をご覧ください。**
  
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
* Apache 2.x
* CentOS 6 x86_64
* PHP 5.5.10 ( +gd +mcrypt )

##開発環境
* Mac OS X 10.9.4
* mi (テキストエディタ)