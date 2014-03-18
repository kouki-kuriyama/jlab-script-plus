jlab-script-plus Ver0.03a
================
  
**2014年3月18日 jlab-script-plus Ver0.03a を公開しました**  
  
jlab-script-plus は保存期間を設定できるアップローダースクリプトです。  
全体の投稿数により削除される仕組みでは無いため、確実に設定した間は閲覧できる状態にしたい場合や長い間保存しておきたくない画像などの公開に向いています。  
このスクリプトは **JLAB - 実況ろだ 明るい分散計画** 向けに作成されたスクリプトですが、それ以外の場所でもアップローダーとして設置していただけます。

##必要環境
* PHP 5.2以上
* GDライブラリ
* php-mcrypt(設定によっては不要)

##ダウンロード
右下の **Download ZIP** からスクリプト一式をダウンロードします。

##ファイル・フォルダ一覧
初期設定では以下の通りにファイル、フォルダを設置することにより動作します。  
[707]は推奨パーミッションを表しています。（サーバにより推奨の設定は異なります。）

* /s [707]
* /t [707]
* /d [707]
* /static-data [707]
 - Encryption.php
 - Thumb.php
* index.php
* upload.php
* delete.php
* masterkey.php
* settings.php
* reg-delete.php

##設置方法
1.settings.php を開き、各項目を設定します。  
2.masterkey.php を開き、settings.phpで設定したマスターキーを設定します。  
3.すべてのファイル・フォルダをアップロードします。  
4.ブラウザから settings.php にアクセスし、static-dataフォルダに setting.dat が作成されたことを確認します。  
5.トップページにアクセスし、画像が正しくアップロードできるか確認します。  
6.CronJobで reg-delete.php に１日１回アクセスするように設定します。(マニュアル削除の場合は不要)  
(※バージョンアップの場合は下の [バージョンアップ方法](https://github.com/kouki-kuriyama/jlab-script-plus#%E3%83%90%E3%83%BC%E3%82%B8%E3%83%A7%E3%83%B3%E3%82%A2%E3%83%83%E3%83%97%E6%96%B9%E6%B3%95)をご覧ください。  

##CronJobの設定
CronJobの時間は午前0時5分頃がおすすめです。  
CronJobの設定できないサーバの場合は、外部から reg-delete.php のアクセスでも動作させることができますので、定期的にブラウザからアクセスするか、GAE等CronJobの設定ができるサーバから reg-delete.php にアクセスすることで同様の動作をさせることができます。
また、CronJob等の定期的なアクセスの設定ができない場合は下記の **マニュアル削除機能** をご覧ください。

##マニュアル削除機能
マニュアル削除機能は Ver0.02a から追加された機能です。  
日付が変わって最初の一枚目がアップロードされるのをトリガーとし、保存期間を超えた画像・サムネイル・ログファイルを削除する機能です。
CronJob等の定期的なアクセスを設定する必要が無いため、簡単に保存期間制限を設けることができます。  
マニュアル削除機能はデフォルトで有効になっています。reg-delete.php による削除をする場合には settings.php でマニュアル削除機能を無効にします。

##バージョンアップ方法
1.**Download ZIP**からスクリプト一式をダウンロードします。  
2.サーバ上の static-data/setting.dat を削除します。  
3.settings.php を開き、各項目を設定します。  
4.masterkey.php 以外のスクリプトをすべて上書きします。  
(※画像やログファイルを削除せずにバージョンアップが可能です)  

##再配布等について
再配布・転載・フォークはご自由にしていただいて構いません。  
バグ修正版の公開等はこのGitHubでのみ行いますので、アップローダー下部にあるGitHubへのリンクは削除されないことをおすすめします。

##更新履歴
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
