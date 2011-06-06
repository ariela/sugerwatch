<?php
require_once 'Console/GetOpt.php';
/**
 * ファイル更新検知ツール「SugerWatch」
 *
 * License:
 * 
 * Copyright 2011 Takeshi Kawamoto
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @author  Takeshi Kawamoto <yuki@transrain.net>
 * @version $Id:$
 * @since   0.0.1
 */
final class SugerWatch
{
    /**
     * SugerWatchのバージョン情報
     * @var string
     */
    const VERSION = '0.0.2';
    /**
     * 標準出力に出力する文字コード
     * @var string 
     */
    private $m_charset = 'UTF-8';
    /**
     * 監視対象ファイル一覧
     * @var array
     */
    private $m_targets = array();
    /**
     * 監視対象ファイル情報
     * @var array
     */
    private $m_files = array();
    /**
     * ログ出力ファイル名
     * @var string
     */
    private $m_logfile;
    /**
     * 監視除外ファイル名
     * @var array
     */
    private $m_exclude = array();
    /**
     * リロードする分数
     * @var int
     */
    private $m_reload = 1;
    /**
     * usageを吐き出す場合にのみtrueが設定される
     * @var boolean
     */
    private $m_usage = false;
    /**
     * フィルターを保持する
     * @var array
     */
    private $m_filters = array();

    /**
     * SugerWatchを実行する
     */
    public function __construct()
    {
        // クラスローダ
        spl_autoload_register(function($className) {
                    if (0 === strncmp($className, 'sugerwatch', 10)) {
                        $classPath = str_replace('\\', '_', $className);
                        $classPath = implode(DIRECTORY_SEPARATOR, explode('_', $classPath));
                        $classPath .= '.php';

                        @include_once $classPath;
                    }
                });

        // 出力エンコードを切り替える
        if (0 === strncmp(PHP_OS, 'WIN', 3)) {
            $this->m_charset = 'SJIS-win';
        }

        // パラメータチェック
        if (!$this->parseArguments()) {
            $this->usage();
            return 1;
        }

        // ファイル情報の取得
        foreach ($this->m_targets as $target) {
            $this->readFiles($target);
        }

        $prevload = time();
        $interval = $this->m_reload * 60;

        $this->stdout('監視処理開始');
        $this->applyFilter('notify', 'system', '監視開始', 'ファイル更新を開始しました。');
        while (true) {

            // 一定時間毎にリストをリロードする
            if ($interval !== 0 && $prevload + $interval < time()) {
                $this->listReload();
                $prevload = time();
            }

            usleep(500000);
            $changed = $this->checkChangeFile();
            if ($changed) {
                if (file_exists($changed)) {
                    $this->m_files[$changed] = filemtime($changed);
                } else {
                    $this->listReload();
                    $prevload = time();
                }

                $text = sprintf('「%s」に変更がありました。', $changed);
                $this->stdout($text);
                $this->applyFilter('notify', 'system', 'ファイルの変更を検知', $text);
                $this->applyFilter('changed', $changed);
            }
        }
    }

    /**
     * 渡された引数をパースする
     */
    private function parseArguments()
    {
        // オプション情報の取得
        $shortoptions = 'c:e:';
        $longoptions = '';
        $console = new Console_GetOpt();
        $args = $console->getopt($console->readPHPArgv(), $shortoptions);
        if (PEAR::isError($args)) {
            $message = substr($args->getMessage(), 16);
            if (preg_match('/option requires an argument -- ?(.+)/', $message, $m)) {
                $this->stdout('「%s」にパラメータが付加されていません。', $m[1]);
            } elseif (preg_match('/option doesn\'t allow an argument -- ?(.+)/', $message, $m)) {
                $this->stdout('「%s」にパラメータが付加されています。', $m[1]);
            } elseif (preg_match('/unrecognized option -- ?(.+)/', $message, $m)) {
                $this->stdout('「%s」は不明なオプションです。', $m[1]);
            } else {
                $this->stdout('原因不明のエラーが発生しました。');
            }
            return false;
        }

        // ディレクトリ/ファイルパスのチェック
        if (empty($args[1])) {
            $this->stdout('監視対象が指定されていません。');
            return false;
        }

        // 必須オプションのチェック
        $foundrequired = false;
        $prm = $args[0];
        foreach ($prm as $l) {
            if ($l[0] === 'c') $foundrequired = true;
        }

        if (!$foundrequired) {
            $this->stdout('必須パラメータが指定されていません。');
            return false;
        }

        if (!$args) {
            return false;
        }

        $options = $args[0];
        $filearr = $args[1];

        // 設定
        $files = empty($filearr) ? array() : array_filter(array_map('realpath', $filearr));
        $config = '';
        $exclude = array();

        foreach ($options as $option) {
            switch ($option[0]) {
                case 'c':
                    // 設定ファイル
                    $config = $option[1];
                    $this->loadConfig($config);
                    break;

                case 'e':
                    // 監視対象外
                    $exclude = array_filter(array_map('realpath', explode(',', $option[1])));
                    break;
            }
        }

        // 設定を保持させる
        $this->m_targets = $files;
        $this->m_exclude = $exclude;

        return true;
    }

    /**
     * 指定されたファイル・ディレクトリを走査して
     * 全てのファイル情報を取得する。
     */
    private function readFiles($target)
    {
        // パスを取得
        $path = realpath($target);

        // ファイルが存在しない場合は終了
        if (!$path) return;

        if (is_dir($path)) {
            // ディレクトリの場合は再帰的に走査する。
            $d = dir($path);
            while (false !== ($entry = $d->read())) {
                if ($entry === '.' || $entry === '..') continue;
                $file = $path . DIRECTORY_SEPARATOR . $entry;
                $this->readFiles($file);
            }
        } elseif (is_file($path)) {
            // ファイルの場合は最終更新日時を取得する
            if ($path === $this->m_logfile) return;
            if ($this->isExclude($path)) return;
            $this->m_files[$path] = filemtime($path);
        }
    }

    /**
     * 指定されたパスが除外されていないかを確認
     * @param string チェックパス
     * @return bool 除外時true / 除外ではない場合はfalse
     */
    private function isExclude($path)
    {
        if (empty($this->m_exclude)) {
            return false;
        }

        if (in_array($path, $this->m_exclude)) {
            return true;
        }

        if (in_array(basename($path), $this->m_exclude)) {
            return true;
        }

        return false;
    }

    /**
     * 監視対象のファイルが更新されていないかを確認する
     * @return string 更新があったファイルのパス。更新未検知時はfalse
     */
    private function checkChangeFile()
    {
        foreach ($this->m_files as $file => $changed) {
            clearstatcache();
            if (!file_exists($file)) return $file;
            if (filemtime($file) > $changed) {
                return $file;
            }
        }
        return false;
    }

    /**
     * 監視ファイルリストを再読み込みする。
     */
    private function listReload()
    {
        $msg = "ファイルリストを再読み込みします。";
        $this->stdout($msg);
        $this->applyFilter('notify', 'system', 'ファイルリスト再読込', $msg);
        $this->m_files = array();
        foreach ($this->m_targets as $target) {
            $this->readFiles($target);
        }
    }

    /**
     * 設定ファイルを読み込み
     */
    private function loadConfig($configFile)
    {
        $configFile = realpath($configFile);
        if (!$configFile) return;

        $config = parse_ini_file($configFile, true);

        if (!$config) {
            $this->stdout('設定ファイル%sのフォーマットが不正です。', $configFile);
            exit(1);
        }

        // システム設定
        if (isset($config['SugerWatch'])) {
            $sys = $config['SugerWatch'];
            $this->m_charset = @$sys['charset'] ? : $this->m_charset;
            $this->m_reload = @$sys['reload'] ? : 1;
            $this->m_logfile = @$sys['log'] ? : null;
            unset($config['SugerWatch']);
        }

        // フィルター構築
        foreach ($config as $filter => $options) {
            $class = '\\sugerwatch\\filter\\' . $filter;
            if (class_exists($class, true)) {
                $obj = new $class($options);
                $obj->setFilters($this->m_filters);
                $this->m_filters[] = $obj;
            }
        }
    }

    /**
     * フィルタを実行する
     */
    private function applyFilter()
    {
        $args = func_get_args();
        $call = array_shift($args);

        foreach ($this->m_filters as $filter) {
            $result = call_user_func_array(array($filter, $call), $args);
            if (!empty($result) && is_string($result)) {
                $this->stdout($result);
            }
        }
    }

    /**
     * 使用方法の出力を行う
     */
    private function usage()
    {
        $this->m_usage = true;
        $out = array();
        $out[] = 'Usage: SugerWatch -c <config> [options] [directory or file]';
        $out[] = 'Options:';
        $out[] = '    -c <config>    必須:SugerWatchの設定ファイル';
        $out[] = '    -e <exclude>   監視対象外のファイル名(カンマ区切りで複数ファイル)';
        foreach ($out as $line) {
            $this->stdout($line);
        }
    }

    /**
     * 標準出力に文字列を1行出力する。
     * @param string $format  出力する文字列(printf形式)
     * @param string $replace [置換する文字列]
     * @param string $...     [置換する文字列]
     */
    private function stdout()
    {
        $argv = func_get_args();
        if (count($argv) <= 1) {
            $msg = isset($argv[0]) ? $argv[0] : '';
        } else {
            $msg = call_user_func_array('sprintf', $argv);
        }

        if (!$this->m_usage) {
            $msg = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
        }

        if ($this->m_charset !== 'UTF-8') {
            $msg = mb_convert_encoding($msg, $this->m_charset, 'UTF-8');
        }
        echo "{$msg}\n";
    }
}