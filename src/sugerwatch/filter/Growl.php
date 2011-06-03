<?php
namespace sugerwatch\filter;

require_once 'Net/Growl.php';
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
 * @since   0.0.2
 */
class Growl extends \sugerwatch\Filter
{
    /**
     * Net_Growlのインスタンスを保持する
     * @var Net_Growl
     */
    private $m_growl;
    /**
     * 初期化
     * @param array $options 
     */
    public function __construct(array $options)
    {
    
        // 接続オプション
        $opt = array(
            'host' => @$options['host'] ?: 'localhost',
            'protocol' => 'tcp',
            'port' => \Net_Growl::GNTP_PORT,
            'timeout' => 15,
            'AppIcon' => @$options['icon'] ?: 'http://transrain.net/growl/info.png',
        );
        
        // 通知オプション
        $notify = array();
        $option = @$options['notification'] ?: array();
        foreach ($option as $line) {
            $line = explode('|', $line);
            
            $k1 = $line[0];
            $k2 = $line[1];
            $v  = $line[2];
            
            if (!isset($notify[$k1])) $notify[$k1] = array();
            $notify[$k1][$k2] = $v;
            /*
            
            */
        }
        
        $app =  @$options['application'] ?: 'SugerWatch';
        $pass = @$options['pass'] ?: '';
        $this->m_growl = \Net_Growl::singleton($app, $notify, $pass, $opt);
        $this->m_growl->register();

    }

    public function notify($type, $title, $message, array $options = array())
    {
        if (!empty($this->m_growl)) {
            $this->m_growl->notify($type, $title, $message, $options);
        } 
    }
}