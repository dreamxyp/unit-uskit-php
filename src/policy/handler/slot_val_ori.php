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

namespace ounun\baidu\unit\kit\policy\handler;


use ounun\baidu\unit\kit\chat\slot;

class slot_val_ori extends handler
{
    /**
     * @return mixed
     */
    public function handle()
    {
        $slots = $this->policy->manager->result_get()->slots_get();
        $s = explode(',', $this->value);
        /**
         * @var $slot slot
         */
        $slot = $slots[$s[0]][$s[1] ?? 0];
        if (!$slot) {
            return null;
        } else {
            return $slot->value_get();
        }
    }
}