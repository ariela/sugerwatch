SugerWatch
==========
ファイルの変更を監視し、変更があった場合にフィルタを実行するアプリケーション。
ファイル変更時に処理を実行させたい場合に利用することができる。

動作環境
--------
PHP 5.3以上

フィルタ一覧
------------
* CompressGz: 指定したファイル名の正規表現にマッチするファイルを変更時にGZ圧縮する。
* Growl: 通知をGrowlで表示する。
* Sass: [Sass](http://sass-lang.com/)でSCSSをCSSにコンパイルする。
* Combine: 指定したファイルを1つのファイルに結合する。

インストール
------------
Openpearからインストールが可能です。

    pear channel-discover openpear.org
    pear install openpear/SugerWatch-alpha

使用方法
--------
利用するにはコマンドラインからSugerWatchを呼び出します。

    sugerwatch -c config.ini ./

作業例(SCSS使用時)
------------------
[style.ini](https://github.com/ariela/sugerwatch/blob/master/ini_sample/style.ini)

    sugerwatch -c style.ini ./

上記方法で実行ディレクトリの*.scssが変更されたときにscssコマンドを実行し、cssにコンパイルした後にgz圧縮を行うことができる。

INIファイルの記述例
-------------------
使いたいフィルタ名をセクションに記述し、設定をキー=値の形式で記述する。
設定方法はフィルタによって異なる。

    [SugerWatch]
    ;charset=SJIS-win ;コンソールに出力する文字コード(未指定時はWINDOWSではSJIS-win、他ではUTF-8)
    reload=0.5       ;30秒毎にファイルを走査する(未指定時は1分毎）
    log=log.txt      ;ログ出力ファイルパス

    [CompressGz]
    file_pattern='\.(css|js)$'

フィルタの設定
--------------

### 本体設定 ###
本体設定は*SugerWatch*セクションにて行う。

|キー    |値 |
|--------|---|
|charset |コンソールに出力する文字コードを指定する。未指定時はWINDOWSの場合はShift-JIS、他のOSではUTF-8で出力される。mb_convert_encodingに使われる値|
|reload  |ファイル走査の間隔秒数を指定する。未指定時は1分毎にファイルを走査して追加されたファイルを調査対象にする。|
|log     |(未実装) コンソールに出力されるメッセージをログに出力する。|

### CompressGz フィルタ ###
CompressGz フィルタ設定は*CompressGz*セクションにて行う。

|キー         |値 |
|-------------|---|
|file_pattern |正規表現で圧縮対象のファイル名を指定する。

### Growl フィルタ ###
Growl フィルタ設定は*Growl*セクションにて行う。

|キー           |値 |
|---------------|---|
|application    |アプリケーション名を設定する。|
|host           |Growlメッセージを送信する先のホスト名・IPアドレスを設定する。|
|pass           |Growlの通知用パスワードを設定する。|
|icon           |アプリケーションのアイコン画像URLを設定する。|
|notification[] |通知設定。複数行設定可能。「メッセージタイプ\\|設定ID\\|設定名」の形式で記述|

### Sass フィルタ ###
Sass フィルタ設定は*Sass*セクションにて行う。

|キー    |値 |
|--------|---|
|charset |出力するCSSの文字コード|
|target  |変換元のSCSSファイル|
|output  |変換先のCSSファイル|
|style   |CSSの出力フォーマット。未指定時はnested。Sassの--style設定。|
|import  |Sassのpartialを配置しているディレクトリ。Windowsの場合、\\を/に変更して記述する。|
|option  |追加するSassのオプション|

### Combine フィルタ ###
Combine フィルタ設定は*Combine*セクションにて行う。
|キー    |値 |
|--------|---|
|target[]|結合情報「結合先\\|結合元1\\|...\\|結合元x」の形式で記述|

TODO
----
* ログ出力の実装
* Phingフィルターの追加

Net_Growlのバグ
---------------
Net_Growl2.2.2には[日本語メッセージが送信できないバグ](http://pear.php.net/bugs/bug.php?id=18589)があります。
Growlフィルタを利用する場合は下記パッチを適応してください。

Net_Growl2.3で修正が取り込まれる予定となっています。

* [Net_Growl](http://pear.php.net/bugs/bug.php?id=18589&edit=12&patch=Growl.php&revision=latest)
* [Net_Growl_Gntp](http://pear.php.net/bugs/bug.php?id=18589&edit=12&patch=Gntp.php&revision=latest)
* [Net_Growl_Udp](http://pear.php.net/bugs/bug.php?id=18589&edit=12&patch=Udp.php&revision=latest)
