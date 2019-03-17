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

/** 根目录 **/
defined('Dir_Root') || define('Dir_Root', realpath(__DIR__.'/../../') . DIRECTORY_SEPARATOR);

require Dir_Root . 'vendor/autoload.php';

$config_file = Dir_Root . 'data/config/service.json';
$config      = json_decode(file_get_contents($config_file), true);

if($config && is_array($config)){
    $config_len = count($config);
    echo "现在可以开始机器人测试了? \n";
    if($config_len  > 1){
        echo "输入 1 - ".count($config).".来选择对应机器人来测试 \n";
    }else{
        echo "输入 1 开始测试 \n";
    }
    foreach ($config as $k=>$v){
        $k2 = $k+1;
        echo "{$k2}:测试机器人：{$v['name']}[id:{$v['service_id']}]\n";
    }
}else{
    exit("配制找不到文件:{$config_file}\n");
}



// print_r($bots);
$stdin   = fopen('php://stdin', 'r');
$choose0 = trim(fgets($stdin));
try{
    $choose        = (int)$choose0 - 1;
    $config_curr   = $config[$choose];
    if($choose < 0 || $choose >= $config_len || empty($config_curr)){
        echo "请安提示输入正确的数据. \n";
        exit;
    }
    $policyManager = \ounun\baidu\unit\kit\chat\manager::instance($config_curr);
}catch ( \Exception $e){
    echo $e->getMessage()."\n";
    exit();
}

echo "Entered bot, you can say something to test. \n";

$access_token = \ounun\baidu\unit\kit\tool\request::access_token_get($config_curr['api_key'],$config_curr['api_secret']);
if(empty($access_token)){
    echo "获取\$access_token有误\n";
    exit();
}else{
    echo "\$access_token:{$access_token}\n";
}

$paras_service = new \ounun\baidu\unit\kit\tool\paras_service('test_'.date("Ymd"),$config_curr['service_id']);

while ($word = trim(fgets($stdin))) {
    try {

        $payload = $paras_service->get($word);
        //unit response
        try{
            $ret = \ounun\baidu\unit\kit\tool\request::rebot_chat($access_token, $payload);
        }catch (\Exception $e){
            exit("Exception:{$e->getMessage()}\n");
        }
        if(false === $ret) {
            echo "Request unit failed!";
            exit(-1);
        }
        //us-kit output
        $output = $policyManager->setRequestParams(['cuid' => 'test_user'])->setQuResults($ret)->output($unitSay);

        if(false === $output) {
            //当unit未召回意图时，返回兜底话术
            echo $unitSay . "\n";
        }else{
            //返回配置的内容，可进行后续处理
            echo json_encode($output['results'], JSON_UNESCAPED_UNICODE) . "\n";
        }
        $botSession = $ret['result']['bot_session'];
    } catch (\Exception $e) {
        echo $e->getMessage() . "\n";
        exit(-1);
    }
}