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

use ounun\baidu\unit\kit\session\object;

/**
 * Class QuResult
 * @package ounun\baidu\unit\kit\dialog
 */
class result_qu
{
    private $intent;

    private $slots;

    private $changedSlots;

    private $say;

    private $serverId;

    private $sessionId;

    /**
     * QuResult constructor.
     */
    function __construct()
    {
        $this->slots = [];
        $this->changedSlots = [];
    }

    /**
     * @return mixed
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param mixed $intent
     * @return result_qu
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
        return $this;
    }

    /**
     * @return slot[][]
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @param slot $slot
     * @return result_qu
     */
    public function addSlot(slot $slot)
    {
        if (isset($this->slots[$slot->getKey()])) {
            $this->slots[$slot->getKey()][] = $slot;
        } else {
            $this->slots[$slot->getKey()] = [$slot];
        }

        return $this;
    }

    /**
     * @param $key
     * @return slot
     */
    public function getSlot($key)
    {
        return $this->slots[$key][0];
    }

    /**
     * @return mixed
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * @param mixed $serverId
     * @return result_qu
     */
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param mixed $sessionId
     * @return result_qu
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSay()
    {
        return $this->say;
    }

    /**
     * @param mixed $say
     * @return result_qu
     */
    public function setSay($say)
    {
        $this->say = $say;
        return $this;
    }

    /**
     * @param object $sessionObject
     */
    public function buildChangedSlots(object $sessionObject)
    {
        $sessionSlotMap = $sessionObject->getSlots();
        foreach ($this->slots as $key => $slots) {
            $sessionSlots = $sessionSlotMap[$key] ?? [];
            if(count($slots) !== count($sessionSlots)) {
                $this->changedSlots[$key] = true;
                continue;
            }
            /**
             * @var string $i
             * @var slot   $slot
             */
            foreach ($slots as $i => $slot) {
                $sessionSlot = $sessionSlots[$i];
                if($slot->getValue() !== $sessionSlot->getValue()) {
                    $this->changedSlots[$key] = true;
                    break;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getChangedSlots()
    {
        return $this->changedSlots;
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
                $slots[] = $slot->getkey();
            }
        }
        return 'Intent: '. $this->intent. ', slots: '.json_encode($slots);
    }
}