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

use ounun\baidu\unit\kit\exception\us_kit_exception;
use Monolog\Logger;

abstract class session_abstract
{
    protected $logger;
    protected $uuid;
    protected $expire;
    protected $conf;
    protected $shouldDelete = false;
    protected $serviceId;

    /**
     * @var $sessionObject object
     */
    protected $sessionObject;

    /**
     * @return string
     */
    protected function getKey()
    {
        return $this->uuid . '_' . $this->serviceId;
    }

    public function clean()
    {
        $this->shouldDelete = true;
    }

    /**
     * @param $uuid
     * @return session_abstract
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return object
     */
    public function getSessionObject()
    {
        return $this->sessionObject;
    }

    /**
     * @param mixed $serviceId
     * @return session_abstract
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    /**
     * AbstractSession constructor.
     * @param Logger $logger
     * @param $conf
     * @throws us_kit_exception
     */
    public function __construct(Logger $logger, $conf)
    {
        $this->logger = $logger;
        if (!isset($conf['expire'])) {
            throw new us_kit_exception('Expire time for session should be set.');
        }
        $this->expire = $conf['expire'];
        $this->conf   = $conf;
    }

    /**
     * @return bool
     */
    public function getShouldDelete()
    {
        return $this->shouldDelete;
    }

    abstract public function read();

    abstract public function write();
}
