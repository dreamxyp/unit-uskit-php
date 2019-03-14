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

use Monolog\Logger;
use ounun\baidu\unit\kit\chat\service;
use ounun\baidu\unit\kit\dialog\result_qu;
use ounun\baidu\unit\kit\exception\us_kit_exception;
use ounun\baidu\unit\kit\parser\parser;
use ounun\baidu\unit\kit\policy\output\fun;
use ounun\baidu\unit\kit\policy\output\out;
use ounun\baidu\unit\kit\session\session_abstract;

/**
 * Class PolicyManager
 * @package ounun\baidu\unit\kit\Policy
 */
class manager
{
    private $policyMap;
    private $requestParams;
    private $session;
    private $parser;
    private $serviceId;

    /**
     * @var $service service
     */
    private $service;
    /**
     * @var $quResult result_qu
     */
    private $quResult;

    public $logger;

    /**
     * PolicyManager constructor.
     * @param session_abstract $session
     * @param parser $parser
     * @param Logger $logger
     */
    public function __construct(session_abstract $session, parser $parser, Logger $logger)
    {
        $this->session = $session;
        $this->parser = $parser;
        $this->logger = $logger;
    }

    /**
     * inject request parameters from client side
     *
     * @param mixed $requestParams
     * @return manager
     * @throws us_kit_exception
     */
    public function setRequestParams($requestParams)
    {
        $this->requestParams = $requestParams;
        if (empty($requestParams['cuid'])) {
            throw new us_kit_exception('cuid should be set in request params.');
        }
        $this->session->setUuid($requestParams['cuid']);
        $this->service->setRequestParams($requestParams);
        return $this;
    }

    /**
     * inject nlu results from NLU providers
     *
     * @param $quResults
     * @return $this
     * @throws us_kit_exception
     */
    public function setQuResults($quResults)
    {
        $quResultMap = $this->parser->parse($quResults);
        if ($this->serviceId) {
            $this->quResult = $quResultMap[$this->serviceId];
            if (!$this->quResult) {
                return $this;
            }
            $this->service->setQuResult($this->quResult);
        } else {
            throw new us_kit_exception('BotId is not set.');
        }
        return $this;
    }

    /**
     * @param service $service
     * @return $this
     */
    public function setService(service $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * load the policy parameters and build Policy class
     *
     * @param $policies
     * @return array
     */
    public function load($policies)
    {
        if (!$this->policyMap) {
            $policyMap = [];
            foreach ($policies as $policy) {
                $trigger = $policy['trigger'];
                $policyTrigger = new trigger($trigger['intent'] ?? '', $trigger['slots'] ?? [], $trigger['changed_slots'] ?? [], $trigger['state'] ?? '');
                $policyTrigger->setLogger($this->logger);
                $params = [];
                if(is_array($policy['params'])) {
                    foreach ($policy['params'] as $param) {
                        $policyParam = new param($param['name'], $param['type'], $param['value'], $param['required'] ?? false, $param['options'] ?? []);
                        $params[$param['name']] = $policyParam;
                    }
                }

                $outputs = [];
                if (is_string($policy['output'])) {
                    $policyOutput = new fun($policy['output']);
                    $outputs[] = $policyOutput->setLogger($this->logger);
                } elseif(is_array($policy['output'])) {
                    foreach ($policy['output'] as $output) {
                        $policyOutput = new out($output['assertion'], $output['session'], $output['result']);
                        $outputs[] = $policyOutput->setLogger($this->logger);
                    }
                }

                $policyObject = new policy($policyTrigger, $params, $outputs, $this);
                $mapKey = empty($trigger['intent']) ? trigger::NON_INTENT : $trigger['intent'];
                if (!isset($policyMap[$mapKey])) {
                    $policyMap[$mapKey] = [];
                }
                $policyMap[$mapKey][] = $policyObject;

            }
            $this->policyMap = $policyMap;
        }
        return $this->policyMap;
    }

    /**
     * return array result or false
     * if returns false, it means that the query is not recalled
     *
     * @param $unitSay
     * @return array|bool
     */
    public function output(&$unitSay = null)
    {
        //no nlu result
        if (!$this->quResult) {
            return false;
        }
        $unitSay = $this->quResult->getSay();
        $this->session->read();
        $allSlots = $this->session->getSessionObject()->getSlots();
        foreach ($this->quResult->getSlots() as $key => $slot) {
            $allSlots[$key] = $slot;
        }
        $this->quResult->buildChangedSlots($this->session->getSessionObject());
        $this->session->getSessionObject()->setSlots($allSlots);
        if (!isset($this->policyMap[$this->quResult->getIntent()])) {
            $this->session->clean();
            $this->logger->debug('Current intent ' . $this->quResult->getIntent() . " doesn't match.");
            return false;
        }
        $this->logger->debug('Current quResult ' . (string)$this->quResult);

        $matchedPolicies = [];
        foreach ($this->policyMap[$this->quResult->getIntent()] as $policy) {
            /**
             * @var $policy policy
             */
            $score = $policy->getPolicyTrigger()->hitTrigger();
            if ($score) {
                $this->logger->debug('Policy matched. Trigger: ' . (string)$policy->getPolicyTrigger());
                $matchedPolicies[] = [
                    'score' => $score,
                    'policy' => $policy,
                ];
            }
        }

        //choose a policy with highest score
        if(count($matchedPolicies)) {
            usort($matchedPolicies, function($a, $b){
                return $a['score']->isGreaterThan($b['score']) ? -1 : 1;
            });
            $policy = $matchedPolicies[0]['policy'];
            $this->logger->debug('Policy chosen. Trigger: ' . (string)$policy->getPolicyTrigger());
            foreach ($policy->getPolicyOutputs() as $policyOutput) {
                /**
                 * @var $policyOutput PolicyOutputInterface
                 */
                $output = $policyOutput->output($this->session);
                if (false === $output) {
                    continue;
                }
                $this->session->write();
                return $output;
            }
            $this->logger->debug('Nothing to output.');
        }

        //do not retry on initial state
        if ($this->session->getSessionObject()->getState() === trigger::INIT_STATE) {
            return false;
        }
        $retry = $this->service->retry();
        $this->session->write();
        return $retry;
    }

    /**
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param mixed $botId
     * @return manager
     */
    public function setServiceId($botId)
    {
        $this->serviceId = $botId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }

    /**
     * @return session_abstract
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return result_qu
     */
    public function getQuResult()
    {
        return $this->quResult;
    }

    /**
     * @return service
     */
    public function getService()
    {
        return $this->service;
    }

}