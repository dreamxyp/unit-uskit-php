<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/15
 * Time: 12:55
 */

namespace ounun\baidu\unit\kit\chat\paras;


abstract class paras
{
    const Log_Id_Prefix = 'uskit_';

    /** @var string =2.0，当前api版本对应协议版本号为2.0，固定值 */
    protected $version  = '2.0';

    abstract public function set();

    /**  */
    abstract public function get(string $query);
}