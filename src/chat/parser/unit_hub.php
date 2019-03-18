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
 * Class unit_hub
 * @package ounun\baidu\unit\kit\chat\parser
 */
class unit_hub implements parser
{
    /**
     * @var
     */
    private $quResultMap;


    /**
     * @param $response
     * @return array
     */
    public function parse($response)
    {
        manager::logs(__CLASS__.':'.__LINE__,'Parse unit hub response: '. json_encode($response));
        if ($response['errno'] != 0) {
            manager::logs(__CLASS__.':'.__LINE__,'Parse unit response failed. Unit respond: ' . json_encode($response));
        }

        $quResultMap = [];
        if(!$response['unit_response']) {
            return $quResultMap;
        }
        foreach ($response['unit_response'] as $unitResponse) {
            if (!$unitResponse['bot_id']) {
                continue;
            }

            $quResult = new result();
            $quResult->server_id_set($unitResponse['bot_id']);
            $intent = empty($unitResponse['response']['schema']['intent']) ? trigger::Non_Intent : $unitResponse['response']['schema']['intent'];
            $quResult->intent_set($intent);
            $slots = $unitResponse['response']['schema']['slots'];
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
            $quResultMap[$unitResponse['bot_id']] = $quResult;
        }

        foreach ($response['bot_session_list'] as $session) {
            $quResult = $quResultMap[$session['bot_id']];
            if(!$quResult) {
                manager::logs(__CLASS__.':'.__LINE__,'Load session failed for bot '. $session['bot_id']);
                continue;
            }
            $quResult->setSessionId($session['bot_session_id']);
        }

        $this->quResultMap = $quResultMap;
        return $quResultMap;
    }

}
