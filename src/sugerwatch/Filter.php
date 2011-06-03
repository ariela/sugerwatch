<?php
namespace sugerwatch;

/**
 * 処理フィルタの基底クラス
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
abstract class Filter
{

    /**
     * SugerWatchから通知がある場合に呼び出されるメソッド
     */
    public function notify()
    {
        
    }

    /**
     * ファイルが更新されたときに呼び出されるメソッド
     * @param string $file 
     */
    public function changed($file)
    {
        
    }
}