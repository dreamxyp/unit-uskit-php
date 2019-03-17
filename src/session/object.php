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


use ounun\baidu\unit\kit\policy\trigger;

class object
{
    private $state;

    private $slots;

    private $context;

    public function __construct()
    {
        $this->slots   = [];
        $this->context = [];
        $this->state   = trigger::State_Init;
    }

    /**
     * @return mixed
     */
    public function state_get()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     * @return object
     */
    public function state_set($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return mixed
     */
    public function context_get()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     * @return object
     */
    public function context_set($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return mixed
     */
    public function slots_get()
    {
        return $this->slots;
    }

    /**
     * @param mixed $slots
     * @return object
     */
    public function slots_set($slots)
    {
        $this->slots = $slots;
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function slot_one_get($key)
    {
        return $this->slots[$key][0];
    }
}
