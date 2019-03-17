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

class factory
{
    /**
     * @param array  $config
     * @param string $service_id
     * @return file
     * @throws \Exception
     */
    public static function instance(array $config, string $service_id)
    {
        $type   = $config['type'];
        $expire = $config['expire'];
        $path   = $config['path'];
        switch ($type) {
            case 'file':
                $session = new file($path,$expire,$type);
                break;
            default:
                if($type){
                    $class = $type;
                    if(!class_exists($class)) {
                        throw new \Exception("Session class '$class' doesn't exist.");
                    }
                    $session = new $class($path,$expire,$type);
                    if(!$session instanceof session) {
                        throw new \Exception('Session class should extend ounun\baidu\unit\kit\session\session_abstract');
                    }
                }else{
                    $session = new file($path,$expire,'file');
                }
                break;
        }
        $session->service_id_set($service_id);
        return $session;
    }
}