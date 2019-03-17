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

class yaml implements loader
{
    /** @var string */
    private $path;

    /** @var cache */
    private $cache;

    /**
     * JsonLoader constructor.
     * @param $path
     */
    function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function load()
    {
        if($this->cache) {
            $strConf = $this->cache->get($this->key_get());
            if(empty($strConf)) {
                $strConf = file_get_contents($this->path);
                $this->cache->set($this->key_get(), $strConf);
            }
            return \Symfony\Component\Yaml\Yaml::parse($strConf);
        }
        return \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->path));
    }

    /**
     * @param cache $cache
     * @return void
     */
    public function cache_set(cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    private function key_get()
    {
        return 'us_conf_' . md5($this->path);
    }
}
