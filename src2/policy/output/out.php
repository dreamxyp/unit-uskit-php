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

namespace ounun\baidu\unit\kit\policy\output;

use ounun\baidu\unit\kit\interfaces\policy_output;
use ounun\baidu\unit\kit\policy\output\assertion\factory;
use ounun\baidu\unit\kit\policy\policy;
use ounun\baidu\unit\kit\session\session;

class out implements policy_output
{
    /**
     * @var $policy policy
     */
    public $policy;

    private $assertion;
    private $session;
    private $result;


    /**
     * PolicyOutput constructor.
     * @param $assertion
     * @param $session
     * @param $result
     */
    public function __construct($assertion, $session, $result)
    {
        $this->assertion = $assertion;
        $this->session = $session;
        $this->result = $result;
    }



    /**
     * @param session $session
     * @return bool|mixed
     */
    public function output(session $session)
    {
        if ($this->assert()) {
            if(empty($this->session['state'])) {
                $session->clean();
            }
            $session->session_object_get()->setState($this->session['state']);
            $context = $session->session_object_get()->getContext();

            $newContext = $this->session['context'];
            array_walk_recursive($newContext, function(&$item) {
                $item = $this->policy->params_replace($item);
            });
            $session->session_object_get()->setContext(array_merge($context, $newContext));

            $results = [];
            $standardOutput = $this->policy->manager->service_get()->getStandardOutput();
            foreach ($this->result as $item) {
                $data = $item['value'];
                if ($item['type'] === 'json') {
                    $data = $this->params_replace($data);
                    $standardOutput = $this->policy->manager->service_get()->getStandardOutput($data);
                    $results[] = ['type' => 'json', 'value' => $data];
                } else {
                    $data = $this->params_replace($data);
                    $results[] = ['type' => $item['type'], 'value' => $data];
                }
            }
            return array_merge($standardOutput, ['results' => $results]);
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function assert()
    {
        if(!$this->assertion) {
            return true;
        }
        foreach ($this->assertion as $assertion) {
            if (empty($assertion['type']) || empty($assertion['value'])) {
                return false;
            }

            $value = $this->policy->params_replace($assertion['value']);
            $type = $assertion['type'];
            $assertion = factory::getInstance($type);
            if(false === $assertion->assert($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param policy $policy
     * @return mixed|void
     */
    public function policy_set(policy $policy)
    {
        $this->policy = $policy;
    }


    /**
     * @param $data
     * @return array|mixed
     * @throws \Exception
     */
    private function params_replace($data)
    {
        if(is_string($data)){
            $data = $this->policy->params_replace($data);
        }elseif(is_array($data)) {
            array_walk_recursive($data, function(&$item) {
                $item = $this->policy->params_replace($item);
            });
        }
        return $data;
    }
}