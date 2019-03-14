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

namespace ounun\baidu\unit\kit\dialog;

class slot
{
    private $key;
    private $value;
    private $normalizedValue;
    private $begin;

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return slot
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return slot
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNormalizedValue()
    {
        return $this->normalizedValue;
    }

    /**
     * @param mixed $normalizedValue
     * @return slot
     */
    public function setNormalizedValue($normalizedValue)
    {
        $this->normalizedValue = $normalizedValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param mixed $begin
     * @return slot
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
        return $this;
    }

}