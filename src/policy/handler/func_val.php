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


use ounun\baidu\unit\kit\exception\us_kit_exception;

class func_val extends handler_abstract
{
    /**
     * @return mixed
     * @throws us_kit_exception
     */
    public function handle()
    {
        $function = explode(':', $this->value);
        $params = explode(',', $function[1]);
        foreach ($params as $index => $param) {
            $params[$index] = $this->policy->replaceParams($param);
        }

        if (!method_exists($this->policy->policyManager->getService(), $function[0])) {
            throw new us_kit_exception("Function '$function[0]' not found in " . get_class($this->policy->policyManager->getService()));
        }
        return call_user_func_array(array($this->policy->policyManager->getService(), $function[0]), $params);
    }
}