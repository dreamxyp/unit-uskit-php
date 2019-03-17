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

namespace ounun\baidu\unit\kit\policy\handler;


use ounun\baidu\unit\kit\interfaces\handler;
use ounun\baidu\unit\kit\Policy\policy;

class factory
{
    /**
     * @param $type
     * @param policy $policy
     * @param $value
     * @param $options
     * @return handler
     * @throws \Exception
     */
    public static function instance($type, policy $policy, $value, $options)
    {
        switch ($type) {
            case 'slot_val':
                return new slot_val($policy, $value);
            case 'ori_slot_val':
                return new slot_val_ori($policy, $value);
            case 'session_state':
                return new session_state($policy, $value);
            case 'qu_intent':
                return new intent_qu($policy, $value);
            case 'func_val':
                return new func_val($policy, $value);
            case 'request_param':
                return new request_param($policy, $value);
            case 'session_context':
                return new session_context($policy, $value);
            case 'json_extractor':
                return new json_extractor($policy, $value);
            case 'http_request':
                return new http_request($policy, $value, $options);
            default:
                throw new \Exception("Param type $type is not supported.");
        }
    }
}