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

class slot
{
    /** @var mixed */
    private $key;

    /** @var mixed */
    private $value;

    /** @var mixed */
    private $value_normalized;

    /** @var mixed */
    private $begin;

    /**
     * @return mixed
     */
    public function key_get()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return $this
     */
    public function key_set($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function value_get()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function value_set($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function value_normalized_get()
    {
        return $this->value_normalized;
    }

    /**
     * @param mixed $value_normalized
     * @return $this
     */
    public function value_normalized_set($value_normalized)
    {
        $this->value_normalized = $value_normalized;
        return $this;
    }

    /**
     * @return mixed
     */
    public function begin_get()
    {
        return $this->begin;
    }

    /**
     * @param mixed $begin
     * @return $this
     */
    public function begin_set($begin)
    {
        $this->begin = $begin;
        return $this;
    }

}