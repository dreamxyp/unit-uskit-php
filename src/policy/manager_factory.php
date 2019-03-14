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

namespace ounun\baidu\unit\kit\policy;

use ounun\baidu\unit\kit\chat\service;
use ounun\baidu\unit\kit\exception\us_kit_exception;
use ounun\baidu\unit\kit\session\factory;

class manager_factory
{

    static private $defaultConfs = [
        'loader' => ['type' => 'json']
    ];

    /**
     * entry of the uskit, build PolicyManager
     *
     * @param array $config_curr
     * @param array $confs
     * @return manager
     * @throws us_kit_exception
     */
    static public function getInstance(array $config_curr, array $confs = [])
    {
        $confs   = array_merge(self::$defaultConfs, $confs);
        $loader  = \ounun\baidu\unit\kit\loader\factory::getInstance($confs['loader'], Dir_Root.$config_curr['conf_path']);
        $conf    = $loader->load();
        $logger  = \ounun\baidu\unit\kit\logger\factory::getInstance($conf['logger']);
        $parser  = \ounun\baidu\unit\kit\parser\factory::getInstance($conf['parser'], $logger);
        $session = factory::getInstance($conf['session'], $logger, $config_curr['service_id']);

        $policies      = $conf['policies'];
        $policyManager = new manager($session, $parser, $logger);
        $retryLimit    = isset($conf['retry_limit']) ? $conf['retry_limit'] : 0;
        if (isset($conf['bot'])) {
            $botClass = $conf['bot'];
            if (!class_exists($botClass)) {
                throw new us_kit_exception("Service class '$botClass' doesn't exist.");
            }

            $service = new $botClass($session, $retryLimit);
            if (!$service instanceof service) {
                throw new us_kit_exception('Bot class should extend ounun\baidu\unit\kit\chat\service');
            }
        } else {
            $service = new service($session, $retryLimit);
        }

        $policyManager->setServiceId($config_curr['service_id'])->setService($service)->load($policies);

        return $policyManager;
    }

}