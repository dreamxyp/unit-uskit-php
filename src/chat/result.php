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

use ounun\baidu\unit\kit\session\object;

/**
 * Class result
 * @package ounun\baidu\unit\kit\chat
 */
class result
{
    private $intent;

    private $slots;

    private $slots_changed;

    private $say;

    private $server_id;

    private $session_id;

    /**
     * QuResult constructor.
     */
    function __construct()
    {
        $this->slots = [];
        $this->slots_changed = [];
    }

    /**
     * @return mixed
     */
    public function intent_get()
    {
        return $this->intent;
    }

    /**
     * @param mixed $intent
     * @return result
     */
    public function intent_set($intent)
    {
        $this->intent = $intent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function server_id_get()
    {
        return $this->server_id;
    }

    /**
     * @param mixed $server_id
     * @return result
     */
    public function server_id_set($server_id)
    {
        $this->server_id = $server_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function session_id_get()
    {
        return $this->session_id;
    }

    /**
     * @param mixed $session_id
     * @return result
     */
    public function session_id_set($session_id)
    {
        $this->session_id = $session_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function say_get()
    {
        return $this->say;
    }

    /**
     * @param mixed $say
     * @return result
     */
    public function say_set($say)
    {
        $this->say = $say;
        return $this;
    }



    /**
     * @param slot $slot
     * @return result
     */
    public function slot_add(slot $slot)
    {
        if (isset($this->slots[$slot->key_get()])) {
            $this->slots[$slot->key_get()][] = $slot;
        } else {
            $this->slots[$slot->key_get()] = [$slot];
        }
        return $this;
    }

    /**
     * @param $key
     * @return slot
     */
    public function slot_one_get($key)
    {
        return $this->slots[$key][0];
    }

    /**
     * @return slot[][]
     */
    public function slots_get()
    {
        return $this->slots;
    }

    /**
     * @param object $sessionObject
     */
    public function slots_changed_build(object $sessionObject)
    {
        $sessionSlotMap = $sessionObject->slots_get();
        foreach ($this->slots as $key => $slots) {
            $sessionSlots = $sessionSlotMap[$key] ?? [];
            if(count($slots) !== count($sessionSlots)) {
                $this->slots_changed[$key] = true;
                continue;
            }
            /**
             * @var string $i
             * @var slot   $slot
             */
            foreach ($slots as $i => $slot) {
                $sessionSlot = $sessionSlots[$i];
                if($slot->value_get() !== $sessionSlot->getValue()) {
                    $this->slots_changed[$key] = true;
                    break;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function slots_changed_get()
    {
        return $this->slots_changed;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $slots = [];
        foreach ($this->slots as $slotGroup) {
            /** @var slot $slot */
            foreach ($slotGroup as $slot) {
                $slots[] = $slot->key_get();
            }
        }
        return 'Intent: '. $this->intent. ', slots: '.json_encode($slots);
    }
}