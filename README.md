SugerWatch
==========
ファイルの変更を監視し、変更があった場合にフィルターを実行するアプリケーション。

PHP5.3の環境で利用可能。

簡単に使用方法
--------------
* 適当な位置へ配置(pear install openpear/SugerWatch-alpha)
* 利用したい位置へ移動
* php インストールパス\SugerWatch.php -c scss.ini .


作業例(SCSS使用時)
------------------
    #!/bin/sh
    sass --unix-newline -t compressed --scss --watch theme.scss:theme.css &
    sugerwatch -c scss.ini -e .git,.sass-cache . &

上記方法でSCSS(Sass)でcssがコンパイルされたときにCSSの更新を検知し、gz圧縮をおこなっている。

INIファイルの記述例
-------------------
使いたいフィルタ名をセクションに記述し、設定をキー=値の形式で記述する。

    [SugerWatch]
    ;charset=SJIS-win ;コンソールに出力する文字コード(未指定時はWINDOWSではSJIS-win、他ではUTF-8)
    reload=0.5       ;30秒毎にファイルを走査する(未指定時は1分毎）
    log=log.txt      ;ログ出力ファイルパス

    [CompressGz]
    file_pattern='\.(css|js)$'

    [Growl]
    application=sugerwatch
    host=localhost
    pass=sugerwatch
    icon=http://transrain.net/growl/info.png
    notification[]='change|display|変更検知'
    notification[]='change|icon|http://transrain.net/growl/change.png'
    notification[]='system|display|システムメッセージ'
    notification[]='system|icon|http://transrain.net/growl/system.png'
    notification[]='success|display|成功'
    notification[]='success|icon|http://transrain.net/growl/ok.png'
    notification[]='error|display|エラー'
    notification[]='error|icon|http://transrain.net/growl/ng.png'

TODO
----
* ログ出力の実装
* Phingフィルターの追加

Net_Growlのバグ
---------------
Net_Growlには[日本語メッセージが送信できないバグ](http://pear.php.net/bugs/bug.php?id=18589)があります。
Growlフィルタを利用する場合は下記パッチを適応してください。

* [Net_Growl_Gntp](http://pear.php.net/bugs/bug.php?id=18589&edit=12&patch=Gntp.php&revision=latest)
* [Net_Growl_Udp](http://pear.php.net/bugs/bug.php?id=18589&edit=12&patch=Udp.php&revision=latest)
