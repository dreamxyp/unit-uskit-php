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

namespace ounun\baidu\unit\kit\policy\output\assertion;

use ounun\baidu\unit\kit\Exception\us_kit_exception;

class factory
{
    /**
     * @param $type
     * @return assertion
     * @throws us_kit_exception
     */
    public static function getInstance($type)
    {
        switch ($type) {
            case 'empty':
                return new empty0();
            case 'not_empty':
                return new not();
            case 'in':
                return new in();
            case 'not_in':
                return new not_in();
            case 'eq':
                return new eq();
            case 'gt':
                return new gt();
            case 'ge':
                return new ge();
            case 'not_eq':
                return new not_eq();
            default:
                if (is_subclass_of($type, assertion::class)) {
                    return new $type();
                } else {
                    throw new us_kit_exception("Assertion type $type is not supported.");
                }
        }
    }
}