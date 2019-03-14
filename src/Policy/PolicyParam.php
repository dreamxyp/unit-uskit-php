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

namespace Baidu\Iov\Kit\Policy;

use Baidu\Iov\Kit\Dialog\Slot;
use Baidu\Iov\Kit\Exception\UsException;
use Baidu\Iov\Kit\Policy\ParamHandler\ParamHandlerFactory;

class PolicyParam
{
    private $name;
    private $type;
    private $value;
    private $required;
    private $options;
    private $param;

    /**
     * @var $policy Policy
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
     * @throws UsException
     */
    public function getParam()
    {
        $handler = ParamHandlerFactory::getInstance($this->type, $this->policy, $this->value, $this->options);
        $this->param = $handler->handle();
        $this->policy->policyManager->logger->debug("Param name: {$this->name}, value: {$this->param}");

        return $this->param;
    }
}