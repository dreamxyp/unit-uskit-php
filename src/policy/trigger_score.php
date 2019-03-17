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

class trigger_score
{
    private $intent_score = 0;

    private $state_score = 0;

    private $slots_score = 0;

    private $slots_changed_score = 0;

    /**
     * @param trigger_score $trigger_score
     * @return bool
     */
    public function is_greater_than(trigger_score $trigger_score)
    {
        if ($this->intent_score > $trigger_score->intent_score_get()) {
            return true;
        }
        if ($this->state_score > $trigger_score->state_score_get()) {
            return true;
        }
        if ($this->slots_score > $trigger_score->slots_score_get()) {
            return true;
        }
        if ($this->slots_changed_score > $trigger_score->slots_changed_score_get()) {
            return true;
        }
        return false;
    }

    /**
     * @param mixed $intent_score
     * @return trigger_score
     */
    public function intent_score_set($intent_score)
    {
        $this->intent_score = $intent_score;
        return $this;
    }


    /**
     * @return mixed
     */
    public function intent_score_get()
    {
        return $this->intent_score;
    }

    /**
     * @param mixed $slots_score
     * @return trigger_score
     */
    public function slots_score_set($slots_score)
    {
        $this->slots_score = $slots_score;
        return $this;
    }






    /**
     * @param mixed $state_score
     * @return trigger_score
     */
    public function state_score_set($state_score)
    {
        $this->state_score = $state_score;
        return $this;
    }

    /**
     * @return mixed
     */
    public function state_score_get()
    {
        return $this->state_score;
    }

    /**
     * @return mixed
     */
    public function slots_score_get()
    {
        return $this->slots_score;
    }

    /**
     * @param mixed $slots_changed_score
     * @return trigger_score
     */
    public function slots_changed_score_set($slots_changed_score)
    {
        $this->slots_changed_score = $slots_changed_score;
        return $this;
    }



    /**
     * @return mixed
     */
    public function slots_changed_score_get()
    {
        return $this->slots_changed_score;
    }
}