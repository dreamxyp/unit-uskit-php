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

use ounun\baidu\unit\kit\exception\us_kit_exception;
use Monolog\Logger;

class factory
{
    /**
     * @param $conf
     * @param Logger $logger
     * @return parser
     * @throws us_kit_exception
     */
    public static function getInstance($conf, $logger)
    {
        $className = null;
        switch ($conf['type']) {
            case 'unit_hub':
                $className = unit_hub::class;
                break;
            case 'unit_bot':
                $className = unit_bot::class;
                break;
            case 'custom':
                if (empty($conf['class'])) {
                    throw new us_kit_exception('Parameter class must be defined when use custom parser.');
                }
                $className = $conf['class'];
                if(!class_exists($className)) {
                    throw new us_kit_exception("Parser class '$className' doesn't exist.");
                }
                break;
            default:
                throw new us_kit_exception('Parser is not set.');
        }

        $parser = new $className($logger);
        if(!$parser instanceof parser) {
            throw new us_kit_exception('Parser class should extend ounun\baidu\unit\kit\Parser\ParserInterface');
        }

        return $parser;
    }
}