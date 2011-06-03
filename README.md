SugerWatch
==========
ファイルの変更を監視し、変更があった場合にフィルターを実行するアプリケーション。

PHP5.3の環境で利用可能。

簡単に使用方法
--------------
* 適当な位置へ配置
* 利用したい位置へ移動
* php インストールパス\SugerWatch.php -c scss.ini .


作業例(SCSS使用時)
------------------
    #!/bin/sh
    sass --unix-newline -t compressed --scss --watch theme.scss:theme.css &
    php /usr/local/sugerwatch/SugerWatch.php -c scss.ini -e .git,.sass-cache . &

上記方法でSCSS(Sass)でcssがコンパイルされたときにCSSの更新を検知し、gz圧縮をおこなっている。

TODO
----
* ログ出力の実装
* コマンドバッチの作成
* Openpearでインストール可能にしたい
* Growlフィルターの追加
* Phingフィルターの追加
