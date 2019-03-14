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

namespace Baidu\Iov\Kit;

use Baidu\Iov\Kit\Api\Service;
use Baidu\Iov\Kit\ConfLoader\LoaderFactory;
use Baidu\Iov\Kit\Exception\UsException;
use Baidu\Iov\Kit\Logger\LoggerFactory;
use Baidu\Iov\Kit\Parser\ParserFactory;
use Baidu\Iov\Kit\Policy\PolicyManager;
use Baidu\Iov\Kit\Session\SessionFactory;

class PolicyManagerFactory
{

    static private $defaultConfs = [
        'loader' => ['type' => 'json']
    ];

    /**
     * entry of the uskit, build PolicyManager
     *
     * @param array $config_curr
     * @param array $confs
     * @return PolicyManager
     * @throws UsException
     */
    static public function getInstance(array $config_curr, array $confs = [])
    {
        $confs   = array_merge(self::$defaultConfs, $confs);
        $loader  = LoaderFactory::getInstance($confs['loader'], Dir_Root.$config_curr['conf_path']);
        $conf    = $loader->load();
        $logger  = LoggerFactory::getInstance($conf['logger']);
        $parser  = ParserFactory::getInstance($conf['parser'], $logger);
        $session = SessionFactory::getInstance($conf['session'], $logger, $config_curr['service_id']);

        $policies      = $conf['policies'];
        $policyManager = new PolicyManager($session, $parser, $logger);
        $retryLimit    = isset($conf['retry_limit']) ? $conf['retry_limit'] : 0;
        if (isset($conf['bot'])) {
            $botClass = $conf['bot'];
            if (!class_exists($botClass)) {
                throw new UsException("Service class '$botClass' doesn't exist.");
            }

            $service = new $botClass($session, $retryLimit);
            if (!$service instanceof Service) {
                throw new UsException('Bot class should extend Baidu\Iov\Kit\Api\Service');
            }
        } else {
            $service = new Service($session, $retryLimit);
        }

        $policyManager->setServiceId($config_curr['service_id'])->setService($service)->load($policies);

        return $policyManager;
    }

}