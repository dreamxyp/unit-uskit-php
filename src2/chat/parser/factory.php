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

namespace ounun\baidu\unit\kit\chat\parser;


use ounun\baidu\unit\kit\interfaces\parser;

class factory
{
    /**
     * @param string $type_class
     * @return parser
     * @throws \Exception
     */
    public static function instance(string $type_class)
    {
        $className = null;
        switch ($type_class) {
            case 'unit_hub':
                $className = unit_hub::class;
                break;
            case 'unit_bot':
                $className = unit_service::class;
                break;
            case 'custom':
                if (empty($conf['class'])) {
                    throw new \Exception('Parameter class must be defined when use custom parser.');
                }
                $className = $conf['class'];
                if(!class_exists($className)) {
                    throw new \Exception("Parser class '$className' doesn't exist.");
                }
                break;
            default:
                if($type_class){
                    $className = $type_class;
                    if(!class_exists($className)) {
                        throw new \Exception("Parser class '$className' doesn't exist.");
                    }
                }else{
                    throw new \Exception('Parser is not set.');
                }
                break;
        }

        $parser = new $className();
        if(!$parser instanceof parser) {
            throw new \Exception('Parser class should extend ounun\baidu\unit\kit\interfaces\parser');
        }
        return $parser;
    }
}