<?php
// Copyright (c) 2018 Baidu, Inc. All Rights Reserved.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

namespace ounun\baidu\unit\kit\loader;

use ounun\baidu\unit\kit\interfaces\cache;
use ounun\baidu\unit\kit\interfaces\loader;

class factory
{
    /**
     * @param string $type
     * @param string $path
     * @param string $cache_class
     * @return loader
     * @throws \Exception
     */
    public static function instance(string $type = 'json', string $path = '', string $cache_class = '')
    {
        // path
        if(empty($path)){
            throw new \Exception('Conf $path is not set.');
        }
        // loader
        switch ($type) {
            case 'json':
                $loader = new json($path);
                break;
            case 'yaml':
                $loader = new yaml($path);
                break;
            default:
                if($type){
                    $class = $type;
                    if(!class_exists($class)) {
                        throw new \Exception("Loader class '$class' doesn't exist.");
                    }
                    $loader = new $class($path);
                    if(!$loader instanceof loader) {
                        throw new \Exception('Loader class should implement ounun\baidu\unit\kit\interfaces\loader');
                    }
                }else{
                    throw new \Exception('Conf loader is not set.');
                }
                break;
        }

        if($cache_class) {
            if(!class_exists($cache_class)) {
                throw new \Exception("Cache class '$cache_class' doesn't exist.");
            }
            $cache = new $cache_class();
            if(!$cache instanceof cache) {
                throw new \Exception('Cache class should implement ounun\baidu\unit\kit\interfaces\cache');
            }
            $loader->cache_set($cache);
        }

        return $loader;
    }
}