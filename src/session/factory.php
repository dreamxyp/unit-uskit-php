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

namespace ounun\baidu\unit\kit\session;

use ounun\baidu\unit\kit\exception\us_kit_exception;
use Monolog\Logger;

class factory
{
    /**
     * @param $conf
     * @param Logger $logger
     * @param $botId
     * @return session_abstract
     * @throws us_kit_exception
     */
    public static function getInstance($conf, $logger, $botId)
    {
        switch ($conf['type']) {
            case 'file':
                $session = new file($logger, $conf);
                break;
            case 'custom':
                $class= $conf['class'];
                if(!class_exists($class)) {
                    throw new us_kit_exception("Session class '$class' doesn't exist.");
                }
                $session = new $class($logger, $conf);
                if(!$session instanceof session_abstract) {
                    throw new us_kit_exception('Session class should extend ounun\baidu\unit\kit\Session\AbstractSession');
                }
                break;
            default:
                $session = new file($logger, $conf);
        }
        $session->setServiceId($botId);
        return $session;
    }
}