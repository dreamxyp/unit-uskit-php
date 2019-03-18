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

namespace ounun\baidu\unit\kit\chat;

use ounun\baidu\unit\kit\interfaces\parser;
use ounun\baidu\unit\kit\policy\output\fun;
use ounun\baidu\unit\kit\policy\output\out;
use ounun\baidu\unit\kit\policy\param;
use ounun\baidu\unit\kit\policy\policy;
use ounun\baidu\unit\kit\policy\trigger;
use ounun\baidu\unit\kit\session\session;
use ounun\baidu\unit\kit\tool\debug;
use ounun\baidu\unit\kit\tool\request;

class manager
{
    /** @var array */
    private $policy_map;

    /** @var array */
    private $request_params;

    /** @var result  */
    private $result;

    /** @var parser  */
    private $parser;



    /** @var service */
    private $service;

    /**
     * manager constructor.
     * @param session $session
     * @param parser $parser
     */
    public function __construct(session $session, parser $parser)
    {
        $this->session = $session;
        $this->parser  = $parser;
    }







    /**
     * load the policy parameters and build Policy class
     *
     * @param $policies
     * @return array
     */
    public function load($policies)
    {
        if (!$this->policy_map)
        {
            $policyMap = [];
            foreach ($policies as $policy) {
                $trigger = $policy['trigger'];
                $policyTrigger = new trigger($trigger['intent'] ?? '', $trigger['slots'] ?? [], $trigger['changed_slots'] ?? [], $trigger['state'] ?? '');
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
                } elseif(is_array($policy['output'])) {
                    foreach ($policy['output'] as $output) {
                        $policyOutput = new out($output['assertion'], $output['session'], $output['result']);
                    }
                }

                $policyObject = new policy($policyTrigger, $params, $outputs, $this);
                $mapKey = empty($trigger['intent']) ? trigger::Non_Intent : $trigger['intent'];
                if (!isset($policyMap[$mapKey])) {
                    $policyMap[$mapKey] = [];
                }
                $policyMap[$mapKey][] = $policyObject;

            }
            $this->policy_map = $policyMap;
        }
        return $this->policy_map;
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
        if (!$this->result) {
            return false;
        }
        $unitSay = $this->result->say_get();
        $this->session->read();
        $allSlots = $this->session->session_object_get()->getSlots();
        foreach ($this->result->slots_get() as $key => $slot) {
            $allSlots[$key] = $slot;
        }
        $this->result->slots_changed_build($this->session->session_object_get());
        $this->session->session_object_get()->setSlots($allSlots);
        if (!isset($this->policy_map[$this->result->intent_get()])) {
            $this->session->clean();
            manager::logs(__CLASS__.':'.__LINE__,'Current intent ' . $this->result->intent_get() . " doesn't match.");
            return false;
        }
        manager::logs(__CLASS__.':'.__LINE__,'Current quResult ' . (string)$this->result);

        $matchedPolicies = [];
        foreach ($this->policy_map[$this->result->intent_get()] as $policy) {
            /**
             * @var $policy policy
             */
            $score = $policy->trigger_get()->hit_trigger();
            if ($score) {
                manager::logs(__CLASS__.':'.__LINE__,'Policy matched. Trigger: ' . (string)$policy->trigger_get());
                $matchedPolicies[] = [
                    'score' => $score,
                    'policy' => $policy,
                ];
            }
        }

        //choose a policy with highest score
        if(count($matchedPolicies)) {
            usort($matchedPolicies, function($a,  $b){
                return $a['score']->isGreaterThan($b['score']) ? -1 : 1;
            });
            $policy = $matchedPolicies[0]['policy'];
            manager::logs(__CLASS__.':'.__LINE__,'Policy chosen. Trigger: ' . (string)$policy->trigger_get());
            foreach ($policy->outputs_get() as $policyOutput) {
                /** @var $policyOutput out */
                $output = $policyOutput->output($this->session);
                if (false === $output) {
                    continue;
                }
                $this->session->write();
                return $output;
            }
            manager::logs(__CLASS__.':'.__LINE__,'Nothing to output.');
        }

        //do not retry on initial state
        if ($this->session->session_object_get()->getState() === trigger::State_Init) {
            return false;
        }
        $retry = $this->service->retry();
        $this->session->write();
        return $retry;
    }

    /**
     * inject request parameters from client side
     *
     * @param mixed $request_params
     * @return manager
     * @throws \Exception
     */
    public function request_params_set($request_params)
    {
        $this->request_params = $request_params;
        if (empty($request_params['cuid'])) {
            throw new \Exception('cuid should be set in request params.');
        }
        $this->session->user_id_set($request_params['cuid']);
        $this->service->request_params_set($request_params);
        return $this;
    }

    /**
     * @return mixed
     */
    public function request_rarams_get()
    {
        return $this->request_params;
    }

    /**
     * inject nlu results from NLU providers
     *
     * @param $results
     * @return $this
     * @throws \Exception
     */
    public function results_set($results)
    {
        $result_map = $this->parser->parse($results);
        $service_id = $this->service->service_id_get();
        if ($service_id) {
            $this->result = $result_map[$service_id];
            if (!$this->result) {
                return $this;
            }
            $this->service->result_set($this->result);
        } else {
            throw new \Exception('$service_id is not set.');
        }
        return $this;
    }

    /**
     * @return result
     */
    public function result_get()
    {
        return $this->result;
    }

    /**
     * @return session
     */
    public function session_get()
    {
        return $this->session;
    }


    /**
     * @return service
     */
    public function service_get()
    {
        return $this->service;
    }

    /**
     * @param service $service
     * @return $this
     */
    public function service_set(service $service)
    {
        $this->service = $service;
        return $this;
    }

    /** @var string access_token */
    static protected $_access_token = '';

    /** @var $this */
    static protected $_instance;

    /**
     * entry of the uskit, build PolicyManager
     * @param array $config
     * @return manager
     * @throws \Exception
     */
    static public function instance(array $config = [])
    {
        if(static::$_instance) {
            return static::$_instance;
        }
        if(empty($config) || !is_array($config) || empty($config['service_id'])){
            throw new \Exception('参数$config必须为有效的数组数据！');
        }
        if(empty(static::$_access_token)){
            if(empty($config['api_secret']) || empty($config['api_key'])){
                throw new \Exception('参数$config[\'api_secret\']与$config[\'api_key\']不能为空！');
            }
            static::$_access_token = request::access_token_get($config['api_key'],$config['api_secret']);
            if(empty(static::$_access_token)){
                throw new \Exception('获取$access_token有误');
            }
        }

        $session   = \ounun\baidu\unit\kit\session\factory::instance($config['session'], $config['service_id']);

        $loader    = \ounun\baidu\unit\kit\loader\factory::instance('json', Dir_Root.$config['loader']['path'],$config['loader']['cache_class']);
        $data      = $loader->load();

        $unit_type = $data['parser']['type'];
        $parser    = \ounun\baidu\unit\kit\chat\parser\factory::instance($unit_type);

        $manager        = new static($session, $parser);
        $retry_limit    = isset($data['retry_limit']) ? $data['retry_limit'] : 0;
        if (isset($data['addon'])) {
            $addonClass = $data['addon'];
            if (!class_exists($addonClass)) {
                throw new \Exception("Addon class '{$addonClass}' doesn't exist.");
            }

            $service = new $addonClass($session, $retry_limit);
            if (!$service instanceof service) {
                throw new \Exception('Addon class should extend ounun\baidu\unit\kit\chat\service');
            }
        } else {
            $service = new service($session, $retry_limit);
        }

        $manager->service_id_set($config['service_id'])->setService($service)->load($data['policies']);

        return $manager;
    }

    /** @var debug */
    static protected $_debug;

    /**
     * @param string $key
     * @param $msg
     * @param int $out_type
     */
    static public function logs(string $key,$msg,$out_type = 0)
    {
        // 1 echo  2 header
        if( 1 == $out_type ){
            print_r([$key=>$msg]);
        }elseif (2 == $out_type){
            debug::header($key,$msg,true);
        }
        if(static::$_debug){
            static::$_debug->logs($key,$msg);
        }
    }
}