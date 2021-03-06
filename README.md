jlab-script-plus Ver0.07
================

**2018年11月19日 jlab-script-plus2 beta1 を公開しました**  
[jlab-script-plus2 beta1のリポジトリはこちら](https://github.com/kouki-kuriyama/jlab-script-plus2)

## 重要情報
無印jlab-script-plusはこのVer0.07が最終バージョンの予定です。  
今後 jlab-script-plus 2(jsp2) として新しいアップローダースクリプトをリリース予定です。  
旧ブラウザの互換性保持などの特段の理由が無い場合は、jlab-script-plus 2に移行準備をオススメ致します。

## 概要
jlab-script-plus は保存期間を設定できるアップローダースクリプトです。  
全体の投稿数により削除される仕組みでは無いため、確実に設定した間は閲覧できる状態にしたい場合や長い間保存しておきたくない画像などの公開に向いています。  
このスクリプトは **JLAB - 実況ろだ 明るい分散計画** 向けに作成されたスクリプトですが、それ以外の場所でもアップローダーとして設置していただけます。

## 必要環境
* PHP 5.3以上推奨
 - 全ての機能を利用するには PHP 5.4 以上推奨です
* GDライブラリ

## ダウンロード
右下の **Download ZIP** からスクリプト一式をダウンロードします。

## ファイル・フォルダ一覧
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

## 設置方法
1. settings.php を開き、各項目を設定します。  
2. すべてのファイル・フォルダをアップロードします。  
3. ブラウザから index.php にアクセスし、staticフォルダに setting.dat と process.dat が作成されたことを確認します。  
4. トップページにアクセスし、画像が正しくアップロードできるか確認します。  

## 再配布等について
再配布・転載・フォークはご自由にしていただいて構いません。  
バグ修正版の公開等はこのGitHubでのみ行いますので、アップローダー下部にあるGitHubへのリンクは削除されないことをおすすめします。

## 動作確認環境
* nginx 1.9.5
* CentOS 6 x86_64
* PHP 5.5.10 ( +gd +mcrypt )

## 開発環境
* Mac OS X 10.11
* mi (テキストエディタ)
