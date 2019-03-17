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


use ounun\baidu\unit\kit\chat\manager;
use ounun\baidu\unit\kit\interfaces\policy_output;
use ounun\baidu\unit\kit\policy\policy;
use ounun\baidu\unit\kit\session\session;

class fun implements policy_output
{
    /**
     * @var $policy policy
     */
    public $policy;
    protected $function;

    /**
     * PolicyFunctionOutput constructor.
     * @param $function
     */
    public function __construct($function)
    {
        $this->function = $function;
    }

    /**
     * @param session $session
     * @return mixed
     * @throws \Exception
     */
    public function output(session $session)
    {
        $function = $this->function;
        manager::logs(__CLASS__.':'.__LINE__,'Output function: '. $function);
        if(method_exists($this->policy->manager->service_get(), $function)) {
            $output = $this->policy->manager->service_get()->$function();
            return $output;
        }else{
            throw new \Exception("Method '$function' doesn't exist.");
        }
    }

    /**
     * @param policy $policy
     */
    public function policy_set(policy $policy)
    {
        $this->policy = $policy;
    }
}