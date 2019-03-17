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
use ounun\baidu\unit\kit\policy\handler\factory;

class param
{
    private $name;

    private $type;

    private $value;

    private $required;

    private $options;

    private $param;

    /**
     * @var $policy policy
     */
    public $policy;

    /**
     * PolicyParam constructor.
     * @param $name
     * @param $type
     * @param $value
     * @param bool $required
     * @param array $options
     */
    public function __construct($name, $type, $value, $required = false, $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->options = $options;
        $this->required = $required;
    }

    /**
     * get the value of param
     *
     * @return mixed
     * @throws \Exception
     */
    public function param_get()
    {
        $handler     = factory::instance($this->type, $this->policy, $this->value, $this->options);
        $this->param = $handler->handle();
        manager::logs(__CLASS__.':'.__LINE__,"Param name: {$this->name}, value: {$this->param}");

        return $this->param;
    }
}