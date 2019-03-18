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

namespace ounun\baidu\unit\kit\chat\parser;

use ounun\baidu\unit\kit\chat\manager;
use ounun\baidu\unit\kit\chat\result;
use ounun\baidu\unit\kit\chat\slot;
use ounun\baidu\unit\kit\interfaces\parser;
use ounun\baidu\unit\kit\policy\trigger;


/**
 * for public unit bot api
 *
 * Class UnitBotParser
 * @package ounun\baidu\unit\kit\Parser
 */
class unit_service implements parser
{
    /** @var array */
    private $result_map;

    /**
     * @param $response
     * @return array
     */
    public function parse($response)
    {
        manager::logs(__CLASS__.':'.__LINE__,'Parse unit bot response: ' . json_encode($response));
        if ($response['error_code'] != 0) {
            manager::logs(__CLASS__.':'.__LINE__,'Parse unit response failed. Unit respond: ' . json_encode($response));
        }

        $result = $response['result'];
        $botId  = $result['bot_id'];
        $botSession = json_decode($result['bot_session'], true);
        $quResult   = new result();
        $quResult->server_id_set($botId);
        $intent     = empty($result['response']['schema']['intent']) ? trigger::Non_Intent : $result['response']['schema']['intent'];
        $quResult->intent_set($intent);
        $slots = $result['response']['schema']['slots'];
        $slotsMap = [];
        foreach ($slots as $unitSlot) {
            $slot = new slot();
            $slot->key_set($unitSlot['name'])
                 ->value_set($unitSlot['original_word'])
                 ->value_normalized_set($unitSlot['normalized_word'])
                 ->begin_set($unitSlot['begin']);
            $slotsMap[] = $slot;
        }
        usort($slotsMap, function(slot $a, slot $b) {
            return $a->begin_get() < $b->begin_get() ? -1 : 1;
        });
        foreach ($slotsMap as $item) {
            $quResult->slot_add($item);
        }
        $quResult->session_id_set($botSession['session_id']);
        if($say = $result['response']['action_list'][0]['say']) {
            $quResult->say_set($say);
        }
        $quResultMap[$botId] = $quResult;
        $this->result_map = $quResultMap;
        return $quResultMap;
    }

}
