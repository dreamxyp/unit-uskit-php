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

namespace Baidu\Iov\Kit\Request;

use GuzzleHttp\Client;

class RequestUnit
{
    const URL = 'https://aip.baidubce.com/rpc/2.0/unit/service/chat';
 // const URL = 'https://aip.baidubce.com/rpc/2.0/unit/bot/chat';



    /**
     * @param $api_key
     * @param $secret_key
     * @return string
     */
    public function getAccessToken($api_key,$secret_key)
    {
        $url = 'https://aip.baidubce.com/oauth/2.0/token';
        $post_data = [
            'grant_type' => 'client_credentials',
            'client_id'  => $api_key,
            'client_secret' => $secret_key
        ];
        $urls = [];
        foreach ( $post_data as $k => $v )
        {
            $urls[] = "{$k}=" . urlencode( $v ) ;
        }
        $post_data = implode('&',$urls);
        $res       = $this->sendPostCurl($url, $post_data);
        if($res){
            $res = json_decode($res,true);
            if($res && $res['access_token']){
                return $res['access_token'];
            }
        }
        return '';
        // var_dump($res);
    }

    /**
     * @param string $accessToken
     * @param array  $payload
     * post parameters for unit, see https://ai.baidu.com/docs#/UNIT-v2-service-API/top
     * there is an example in src/Tests/service.php
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function requestUnit($accessToken, $payload)
    {
        if (!isset($payload['log_id'])) {
            throw new \Exception('log_id is required.');
        }

        $payload['log_id'] = self::LOG_ID_PREFIX . $payload['log_id'];
        $header = ['Content-Type: application/json'];
        $ret    = $this->sendPost(self::URL . '?access_token=' . $accessToken, json_encode($payload), $header);
        $ret    = json_decode($ret, true);
        if(!isset($ret['error_code']) || $ret['error_code'] != 0) {
            throw new \Exception('Unit request failed, error_code: '. $ret['error_code']. ', error_msg: '. $ret['error_msg']);
        }
        return $ret;
    }

    /**
     * @param string $url
     * @param mixed  $data
     * @param array  $header
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendPost($url, $data, $header = [])
    {
        $client = new Client();
        $res = $client->request('post', $url, ['body' => $data, 'header' => $header]);
        return $res->getBody();
    }

    /**
     * @param string $url
     * @param string $param
     * @return bool|mixed
     */
    private function sendPostCurl($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }

        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
        $data = curl_exec($curl);//运行curl
        curl_close($curl);

        return $data;
    }
}