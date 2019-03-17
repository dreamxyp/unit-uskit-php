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


use ounun\baidu\unit\kit\chat\manager;
use ounun\baidu\unit\kit\interfaces\policy_output;

class policy
{
    /** @var trigger  */
    private $trigger;

    /** @var array */
    private $params;

    /** @var array */
    private $outputs;

    /** @var $manager manager  */
    public $manager;

    /**
     * Policy constructor.
     * @param trigger $policyTrigger
     * @param $policyParams
     * @param $policyOutputs
     * @param manager $policyManager
     */
    public function __construct(trigger $policyTrigger, array $policyParams, array $policyOutputs, manager $policyManager)
    {
        $this->trigger = $policyTrigger;
        $this->params  = $policyParams;
        $this->outputs = $policyOutputs;
        $this->manager = $policyManager;

        $this->trigger->policy = $this;
        foreach ($this->outputs as $policyOutput) {
            /**
             * @var $policyOutput policy_output
             */
            $policyOutput->policy_set($this);
        }
        foreach ($this->params as $policyParam) {
            /**
             * @var $policyParam param
             */
            $policyParam->policy = $this;
        }
    }

    /**
     * @return trigger
     */
    public function trigger_get()
    {
        return $this->trigger;
    }

    /**
     * @return mixed
     */
    public function outputs_get()
    {
        return $this->outputs;
    }

    /**
     * @return mixed
     */
    public function params_get()
    {
        return $this->params;
    }
    /**
     * @param $str
     * @return mixed
     * @throws \Exception
     */
    public function params_replace($str)
    {
        $params  = $this->params_get();
        $matches = [];
        preg_match_all('/{%([a-zA-Z\-_]+)%}/', $str, $matches);
        foreach ($matches[1] as $key => $match) {
            /**
             * @var $param param
             */
            $param = $params[$match];
            if (!$param) {
                throw new \Exception('Param ' . $match . ' not exists.');
            }
            $value = $param->param_get();
            if (is_array($value)) {
                $value = json_encode($value);
                $str = str_replace($matches[0][$key], $value, $str);
                $str = json_decode($str, true);
            } else {
                $str = str_replace($matches[0][$key], $value, $str);
            }
        }
        return $str;
    }
}