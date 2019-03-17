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

use ounun\baidu\unit\kit\chat\manager;

/**
 * Basic file session, only works on single server architecture
 *
 * Class FileSession
 * @package ounun\baidu\unit\kit\Session
 */
class file extends session
{
    /**
     * @return object
     */
    public function read()
    {
        $filename = $this->_filename();
        if (!is_file($filename)) {
            $this->session_object = new object();
        } elseif (time() - filemtime($filename) > $this->expire) {
            unlink($filename);
            $this->session_object = new object();
        } else {
            $this->session_object = unserialize(file_get_contents($filename));
        }
        manager::logs(__CLASS__.':'.__LINE__,'Read Session: ' . serialize($this->session_object));
        return $this->session_object;
    }

    /**
     * write file session
     */
    public function write()
    {
        $filename = $this->_filename();
        if ($this->should_delete && file_exists($filename)) {
            manager::logs(__CLASS__.':'.__LINE__,'Delete Session: ' . $this->key_get());
            unlink($filename);
        } else {
            manager::logs(__CLASS__.':'.__LINE__,'Write Session: ' . serialize($this->session_object));
            file_put_contents($filename, serialize($this->session_object));
        }
    }

    /**
     * @return string
     */
    protected function _filename()
    {
        $md5  = md5($this->key_get());
        $path = Dir_Root.'data/session/';
        $path = "{$path}{$md5[0]}{$md5[1]}/{$md5[2]}{$md5[3]}/";
        $file = $path.substr($md5,4);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $file;
    }
}
