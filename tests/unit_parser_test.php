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

use ounun\baidu\unit\kit\dialog\result_qu;
use ounun\baidu\unit\kit\dialog\slot;
use ounun\baidu\unit\kit\logger\factory;
use ounun\baidu\unit\kit\parser\unit_bot;
use PHPUnit\Framework\TestCase;

class unit_parser_test extends TestCase
{
    /**
     * @return \ounun\baidu\unit\kit\parser\parser
     * @throws \Exception
     */
    public function testParserFactory()
    {
        $logger = factory::getInstance([
            'handler' => 'stream',
            'args' => [
                'php://stderr',
                'critical'
            ]
        ]);
        $parser = \ounun\baidu\unit\kit\parser\factory::getInstance([
            'type' => 'unit_bot',
        ], $logger);

        $this->assertInstanceOf(unit_bot::class, $parser);
        return $parser;
    }

    /**
     * @depends testParserFactory
     * @param unit_bot $parser
     * @return mixed
     * @throws \Exception
     */
    public function testUnitBotParser(unit_bot $parser)
    {
        $response = json_decode(file_get_contents(__DIR__ . 'res/response1.json'), true);
        $quResultMap = $parser->parse($response);
        $this->assertArrayHasKey('11505', $quResultMap);
        $quResult = $quResultMap['11505'];
        $this->assertInstanceOf(result_qu::class, $quResult);
        return $quResult;
    }

    /**
     * @depends testUnitBotParser
     * @param result_qu $quResult
     */
    public function testQuResult(result_qu $quResult)
    {
        $this->assertEquals('INTENT_ADJUST_QUOTA', $quResult->getIntent());
        $slots = $quResult->getSlots();
        foreach ($slots as $slot) {
            $slot = current($slot);
            $this->assertInstanceOf(slot::class, $slot);
            $this->assertEquals('user_method', $slot->getKey());
        }
    }

}