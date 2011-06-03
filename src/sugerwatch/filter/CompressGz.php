<?php
namespace sugerwatch\filter;

/**
 * 変更のあったファイルをGZ圧縮するフィルタ
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
class CompressGz extends \sugerwatch\Filter
{
    /**
     * 対象ファイルのパターンを保持する
     * @var string
     */
    private $m_filepattern;

    /**
     * インスタンス化
     * @param array $options 
     */
    public function __construct(array $options)
    {
        $this->m_filepattern = @$options['file_pattern'] ? : '*';
    }

    /**
     * ファイルが更新されたときに呼び出されるメソッド
     * @param string $file 
     */
    public function changed($file)
    {
        $pattern = $this->m_filepattern;
        if (preg_match("/{$pattern}/", $file)) {
            // 設定されているパターンに当てはまるファイルのGZファイルを生成する
            $gzf = $file . '.gz';
            $fp = gzopen($gzf, 'wb');
            flock($fp, LOCK_EX);
            gzwrite($fp, file_get_contents($file), filesize($file));
            gzclose($fp);
        }
    }
}