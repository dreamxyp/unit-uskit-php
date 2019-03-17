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

class trigger
{
    /** @var string  */
    const Non_Intent = 'us_non_intent';

    /** @var string  */
    const State_Init = 'us_init';

    /** @var string  */
    const State_Any  = 'us_any';

    /**
     * @var $policy policy
     */
    public $policy;

    protected $intent;

    protected $slots;

    protected $slots_changed;

    protected $state;

    /**
     * PolicyTrigger constructor.
     * @param $intent
     * @param $slots
     * @param $changedSlots
     * @param $state
     */
    public function __construct($intent, $slots, $changedSlots, $state)
    {
        if (!empty($intent)) {
            $this->intent = $intent;
        } else {
            $this->intent = self::Non_Intent;
        }
        if (!empty($slots) && is_array($slots)) {
            $this->slots = $slots;
        } else {
            $this->slots = [];
        }
        if (!empty($changedSlots) && is_array($changedSlots)) {
            $this->slots_changed = $changedSlots;
        } else {
            $this->slots_changed = [];
        }

        $this->state = $state;
    }

    /**
     * @return trigger_score|bool
     */
    public function hit_trigger()
    {
        $session = $this->policy->manager->session_get();
        $quResult = $this->policy->manager->result_get();
        $score = new trigger_score();
        //check intent constraint
        if ($this->intent == $quResult->intent_get()) {
            $score->intent_score_set(1);
        }else{
            manager::logs(__CLASS__.':'.__LINE__,"[Trigger] intent doesn't match, our intent: $this->intent, quResult intent " . $quResult->intent_get());
            return false;
        }

        if(!empty($this->state)){
            //check array state constraint
            $currentState = $session->session_object_get()->getState();
            if(is_array($this->state)) {
                if(in_array($currentState, $this->state)) {
                    $score->state_score_set(1);
                }else{
                    manager::logs(__CLASS__.':'.__LINE__,"[Trigger] state doesn't match, required state " . implode(', ', $this->state) . ", current state " . $currentState);
                    return false;
                }
            }

            //check string state constraint
            if (is_string($this->state)) {
                if($this->state == $currentState) {
                    $score->state_score_set(1);
                }else{
                    manager::logs(__CLASS__.':'.__LINE__,"[Trigger] state doesn't match, required state $this->state, current state " . $currentState);
                    return false;
                }
            }
        }

        //check slots constraint
        if (count($this->slots) === count(array_intersect($this->slots, array_keys($quResult->slots_get())))) {
            $score->slots_score_set(count($this->slots));
        }else{
            manager::logs(__CLASS__.':'.__LINE__,"[Trigger] slots doesn't match.");
            return false;
        }

        //check changed slots constraint
        if (count($this->slots_changed) === count(array_intersect($this->slots_changed, array_keys($quResult->slots_changed_get())))) {
            $score->slots_changed_score_set(count($this->slots_changed));
        }else{
            manager::logs(__CLASS__.':'.__LINE__,"[Trigger] changed slots doesn't match.");
            return false;
        }
        return $score;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (is_string($this->state)) {
            $state = $this->state;
        } else {
            $state = json_encode($this->state);
        }
        if (count($this->slots)) {
            $slots = ', slots: ' . json_encode($this->slots);
        } else {
            $slots = '';
        }
        if (count($this->slots_changed)) {
            $changedSlots = ', changed slots: ' . json_encode($this->slots_changed);
        } else {
            $changedSlots = '';
        }
        return "Intent: $this->intent, state: $state" . $slots . $changedSlots;
    }
}