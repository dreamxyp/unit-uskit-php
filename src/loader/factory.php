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

use ounun\baidu\unit\kit\exception\us_kit_exception;
use ounun\baidu\unit\kit\cache\cache;

class factory
{
    /**
     * @param $conf
     * @param $path
     * @return loader
     * @throws us_kit_exception
     */
    public static function getInstance($conf, $path)
    {
        switch ($conf['type']) {
            case 'json':
                $loader = new json($path);
                break;
            case 'yaml':
                $loader = new yaml($path);
                break;
            case 'custom':
                $class = $conf['class'];
                if(!class_exists($class)) {
                    throw new us_kit_exception("Loader class '$class' doesn't exist.");
                }
                $loader = new $class($path);
                if(!$loader instanceof loader) {
                    throw new us_kit_exception('Loader class should implement ounun\baidu\unit\kit\ConfLoader\LoaderInterface');
                }
                break;
            default:
                throw new us_kit_exception('Conf loader is not set.');
        }

        if(!empty($conf['cache_class'])) {
            $cacheClass = $conf['cache_class'];
            if(!class_exists($cacheClass)) {
                throw new us_kit_exception("Cache class '$cacheClass' doesn't exist.");
            }
            $cache = new $cacheClass();
            if(!$cache instanceof cache) {
                throw new us_kit_exception('Cache class should implement ounun\baidu\unit\kit\ConfLoader\CacheInterface');
            }
            $loader->setCache($cache);
        }

        return $loader;
    }
}