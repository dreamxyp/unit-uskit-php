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

namespace ounun\baidu\unit\kit\parser;

use ounun\baidu\unit\kit\dialog\result_qu;
use ounun\baidu\unit\kit\dialog\slot;
use ounun\baidu\unit\kit\Policy\trigger;
use Monolog\Logger;

/**
 * for public unit bot api
 *
 * Class UnitBotParser
 * @package ounun\baidu\unit\kit\Parser
 */
class unit_bot implements parser
{
    /**
     * @var Logger $_logger
     */
    private $_logger;
    /**
     * @var
     */
    private $quResultMap;

    /**
     * UnitBotParser constructor.
     * @param Logger $logger
     */
    function __construct(Logger $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * @param $response
     * @return array
     */
    public function parse($response)
    {
        $this->_logger->debug('Parse unit bot response: ' . json_encode($response));
        if ($response['error_code'] != 0) {
            $this->_logger->warning('Parse unit response failed. Unit respond: ' . json_encode($response));
        }

        $result = $response['result'];
        $botId = $result['bot_id'];
        $botSession = json_decode($result['bot_session'], true);
        $quResult = new result_qu();
        $quResult->setServerId($botId);
        $intent = empty($result['response']['schema']['intent']) ? trigger::NON_INTENT : $result['response']['schema']['intent'];
        $quResult->setIntent($intent);
        $slots = $result['response']['schema']['slots'];
        $slotsMap = [];
        foreach ($slots as $unitSlot) {
            $slot = new slot();
            $slot->setKey($unitSlot['name'])
                ->setValue($unitSlot['original_word'])
                ->setNormalizedValue($unitSlot['normalized_word'])
                ->setBegin($unitSlot['begin']);
            $slotsMap[] = $slot;
        }
        usort($slotsMap, function(slot $a, slot $b) {
            return $a->getBegin() < $b->getBegin() ? -1 : 1;
        });
        foreach ($slotsMap as $item) {
            $quResult->addSlot($item);
        }
        $quResult->setSessionId($botSession['session_id']);
        if($say = $result['response']['action_list'][0]['say']) {
            $quResult->setSay($say);
        }
        $quResultMap[$botId] = $quResult;
        $this->quResultMap = $quResultMap;
        return $quResultMap;
    }

}
