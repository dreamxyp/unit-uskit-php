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

use ounun\baidu\unit\kit\session\session;

class service
{
    /** @var int */
    protected $service_id;

    /** @var int */
    protected $retry_limit;

    /** @var $session session */
    protected $session;

    /** @var $result result */
    protected $result;

    /** @var array */
    protected $request_params;

    /**
     * Bot constructor.
     * @param session $session
     * @param int              $retry_limit
     */
    public function __construct(session $session, int $retry_limit)
    {
        $this->session     = $session;
        $this->retry_limit = $retry_limit;
    }

    /**
     * @return mixed
     */
    public function service_id_get()
    {
        return $this->service_id;
    }

    /**
     * @param string $service_id
     * @return $this
     */
    public function service_id_set($service_id)
    {
        $this->service_id = $service_id;
        return $this;
    }

    /**
     * @param result $result
     * @return service
     */
    public function result_set($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param mixed $requestParams
     * @return service
     */
    public function request_params_set($requestParams)
    {
        $this->request_params = $requestParams;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    public function session_context_set($key, $value)
    {
        $context = $this->session->session_object_get()->context_get();
        $context[$key] = $value;
        $this->session->session_object_get()->set_context($context);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function session_context_get($key)
    {
        $context = $this->session->session_object_get()->context_get();
        return $context[$key];
    }

    /**
     * @param $key
     * @return mixed
     */
    public function slot_get($key)
    {
        return $this->session->session_object_get()->getSlot($key);
    }

    /**
     * @param $cardType
     * @param $intent
     * @param $tts
     * @param array $data
     * @return array
     */
    public function result($cardType, $intent, $tts = '', $data = array())
    {
        $retryTime = $this->session_context_get('retry_time');
        if (!$retryTime) {
            $this->session_context_set('last_card_type', $cardType);
            $this->session_context_set('last_intent', $intent);
            $this->session_context_set('last_tts', $tts);
            $this->session_context_set('last_data', $data);
            $this->session_context_set('retry_tts', null);
        }

        return array_merge($this->standard_output_get($data), ['results' =>
            [[
                'type'  => 'json',
                'value' => array_merge([
                                            'card_type' => $cardType,
                                            'intent' => $intent,
                                            'tts' => $tts,
                                       ], $data)]]
        ]);
    }

    /**
     * set the next state, reset the retry time
     *
     * @param $state
     */
    public function state_set($state)
    {
        $this->session_context_set('retry_time', 0);
        $this->session->session_object_get()->setState($state);
    }

    /**
     * @param $tts
     * @return $this
     */
    protected function retry_tts_set($tts)
    {
        $this->session_context_set('retry_tts', $tts);
        return $this;
    }

    /**
     * @return array|bool
     */
    public function retry()
    {
        $cardType = $this->session_context_get('last_card_type');
        $intent = $this->session_context_get('last_intent');
        $tts = $this->session_context_get('retry_tts') ? $this->session_context_get('retry_tts') : $this->session_context_get('last_tts');
        $data = $this->session_context_get('last_data');
        $retryTime = $this->session_context_get('retry_time');
        $this->session_context_set('retry_time', $retryTime + 1);
        if ($retryTime >= $this->retry_limit) {
            //when exceeding the limit of retry, clean the session and exit
            $this->session->clean();
            return false;
        } else {
            return $this->result($cardType, $intent, $tts, $data);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function standard_output_get(&$data = [])
    {
        if(key_exists('bot_session_id', $data)) {
            $sessionId = $data['bot_session_id'];
            unset($data['bot_session_id']);
        }else{
            $sessionId = $this->result->session_id_get();
        }

        // you can set a confidence score for the result
        // default value is 100
        if(key_exists('score', $data)) {
            $score = intval($data['score']);
            unset($data['score']);
        }else{
            $score = 100;
        }

        if($this->session->should_delete_get()){
            $sessionId = '';
        }

        return [
            'raw_query' => $this->request_params['word'] ?? '',
            'bot_id' => $this->result->server_id_get(),
            'bot_session_id' => $sessionId,
            'score' => $score,
        ];
    }
}