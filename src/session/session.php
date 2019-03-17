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



abstract class session
{
    /** @var int */
    protected $expire;

    /** @var string */
    protected $type;

    /** @var string */
    protected $path;

    /** @var string */
    protected $user_id;

    /** @var bool */
    protected $should_delete = false;

    /** @var string */
    protected $service_id;

    /** @var object  */
    protected $session_object;

    /**
     * session_abstract constructor.
     * @param string $path
     * @param int $expire
     * @param string $type
     * @throws \Exception
     */
    public function __construct(string $path = 'data/session/',int $expire = 300, string $type = 'file')
    {
        if (empty($expire)) {
            throw new \Exception('Expire time for session should be set.');
        }
        $this->path    = $path;
        $this->expire  = $expire;
        $this->type    = $type;
    }

    /**
     * @return string
     */
    protected function key_get()
    {
        return $this->user_id . '_' . $this->service_id;
    }

    /**
     * @param $user_id
     * @return session
     */
    public function user_id_set($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return object
     */
    public function session_object_get()
    {
        return $this->session_object;
    }

    /**
     * @param string $service_id
     * @return session
     */
    public function service_id_set(string $service_id)
    {
        $this->service_id = $service_id;
        return $this;
    }

    /**
     * @return bool
     */
    public function should_delete_get()
    {
        return $this->should_delete;
    }

    abstract public function read();

    abstract public function write();

    public function clean()
    {
        $this->should_delete = true;
    }
}
