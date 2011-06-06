<?php
namespace sugerwatch\filter;

/**
 * 変更のあったSassファイルをCSS変換するフィルタ
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
class Sass extends \sugerwatch\Filter
{

    private $m_options = '--unix-newline --scss';
    private $m_target;
    private $m_output;
    private $m_style = 'nested';
    private $m_import;
    
    /**
     * インスタンス化
     * @param array $options 
     */
    public function __construct(array $options)
    {
        $this->m_options = @$options['option'] ?: $this->m_options;
        $this->m_target = @$options['target'] ?: '';
        $this->m_output = @$options['output'] ?: '';
        $this->m_style = @$options['style'] ?: '';
        $this->m_import = @$options['import'] ?: '';
    }

    /**
     * ファイルが更新されたときに呼び出されるメソッド
     * @param string $file 
     */
    public function changed($file)
    {
        if (preg_match("/\.scss$/", $file)) {
            $cmd = sprintf('sass %s', $this->m_options);
            if (!empty($this->m_import)) {
                $cmd .= ' -I ' . $this->m_import;
            }
            if (!empty($this->m_style)) {
                $cmd .= ' -t ' . $this->m_style;
            }
            $cmd .= sprintf(' --stdin < %s > %s', $this->m_target, $this->m_output);
            exec($cmd);

            $this->applyFilter('notify', 'success', 'SCSS', "「{$file}」をCSS化しました。");
        }
    }
}