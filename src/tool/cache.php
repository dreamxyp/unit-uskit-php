<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/15
 * Time: 15:44
 */

namespace ounun\baidu\unit\kit\tool;


class cache implements \ounun\baidu\unit\kit\interfaces\cache
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return include $this->_filename($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set($key, $value)
    {
        $str	= var_export($value,    true);
        file_put_contents($this->_filename($key),'<?php '."return {$str};".'?>');
        return $this;
    }

    /**
     * @return string
     */
    protected function _filename($key)
    {
        $md5  = md5($key);
        $path = Dir_Root.'data/cache/';
        $path = "{$path}{$md5[0]}{$md5[1]}/{$md5[2]}{$md5[3]}/";
        $file = $path.substr($md5,4);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $file;
    }
}