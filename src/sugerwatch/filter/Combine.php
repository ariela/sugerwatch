<?php
namespace sugerwatch\filter;

/**
 * 対象ファイルに変更があった場合、ファイルを連結するフィルタ
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
class Combine extends \sugerwatch\Filter
{
    
    private $m_targets = array();

    /**
     * インスタンス化
     * @param array $options 
     */
    public function __construct(array $options)
    {
        $target = @$options['target'] ?: array();
        
        foreach ($target as $line) {
            $line = explode('|', $line);
            if (count($line) <= 1) continue;
            
            $row = array();
            
            $row['file'] = array_shift($line);
            $row['list'] = $line;
            
            $this->m_targets[] = $row;
        }
    }

    /**
     * ファイルが更新されたときに呼び出されるメソッド
     * @param string $file 
     */
    public function changed($file)
    {
        foreach ($this->m_targets as $target) {
            if (in_array(basename($file), $target['list'])) {
                $dir = dirname($file);
                $output = $dir . '/' . $target['file'];
                
                $fp = fopen($output, 'wb');
                flock($fp, LOCK_EX);
                
                foreach ($target['list'] as $line) {
                    $line = realpath($dir . '/' . $line);
                    if ($line) {
                        $buf = file_get_contents($line) . "\n";
                        fwrite($fp, $buf);
                    }
                }
                fclose($fp);
            }
        }
    }
}