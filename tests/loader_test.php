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
namespace tests;

use ounun\baidu\unit\kit\loader\factory;
use ounun\baidu\unit\kit\loader\json;
use ounun\baidu\unit\kit\loader\yaml;
use PHPUnit\Framework\TestCase;

class loader_test extends TestCase
{
    /** @throws \Exception */
    public function testJsonLoader()
    {
        $loaderConf = [
            'type' => 'json',
        ];

        $loader = factory::instance($loaderConf, $this->getConfigPath() . 'quota_adjust.json');
        $this->assertInstanceOf(json::class, $loader);
        $conf = $loader->load();
        $this->assertArrayHasKey('policies', $conf);
    }

    /** @throws \Exception */
    public function testYamlLoader()
    {
        $loaderConf = [ 'type' => 'yaml', ];
        $loader     = factory::instance($loaderConf, $this->getConfigPath() . 'quota_adjust.yml');
        $this->assertInstanceOf(yaml::class, $loader);
        $conf = $loader->load();
        $this->assertArrayHasKey('policies', $conf);
    }

    /** @return string */
    private function getConfigPath()
    {
        return __DIR__ . '/../config/';
    }
}