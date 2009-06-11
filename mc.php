<?php
/* mc 的扩展类 */

class mc extends Memcache implements ArrayAccess
{
    protected $default_timeout = 60; //默认的过期时间
    protected $default_flag = 0; //默认标志
    
    protected $namespace_timeout = array(); //名称空间过期时间
    protected $namespace_flag = array(); //默认标志
    
    //构造函数
    function __construct()
    {
        
    }
    
    //获取变量
    public function get($key)
    {
        $result = parent::get($key);
        if ($result === NULL || $result === false)
        {
            return false;
        }
        else
        {
            return $result;
        }
    }
    
    //设置变量
    public function set($key, $value, $expire = null, $flag = null)
    {
        isset($expire) or $expire = $this->get_default_timeout();
        isset($flag) or $flag = $this->get_default_flag();

        return parent::set($key, $value, $flag, $expire);
    }

    //名称空间变量获取
    public function namespace_get($namespace, $key)
    {
        $key = $namespace . $this->get_ns_key($namespace) . $key;

        return $this->get($key);
    }

    //名称空间变量设置
    public function namespace_set($namespace, $key, $value, $expire = null, $flag = null)
    {
        isset($expire) or $expire = $this->get_namespace_timeout($namespace);
        isset($flag) or $flag = $this->get_namespace_flag($namespace);
        
        $key = $namespace . $this->get_ns_key($namespace) . $key;

        return $this->set($key, $value, $expire, $flag);
    }
    
    //名称空间删除
    public function namespace_delete($namespace, $key = null, $timeout = 0)
    {
        if (empty($key))
        {
            return $this->delete($namespace, $timeout);
        }
        
        
        $key = $namespace . $this->get_ns_key($namespace) . $key;

        return $this->delete($key, $timeout);
    }
    
    //生成一个公共key
    private function get_ns_key($namespace)
    {
        if (!$ns_key = $this->get($namespace))
        {
            $ns_key = time();
            $this->set($namespace, $ns_key, 86400); // 24小时
        }

        return $ns_key;
    }
    
    //implements ArrayAccess
    function offsetExists($offset)
    {
        $tmp = $this->offset_parse($offset);
 
        if ($tmp['namespace'])
        {
            return $this->namespace_get($tmp['namespace'], $tmp['key']) !== false;
        }
        else
        {
            return $this->get($tmp['key']) !== false;
        }
    }
    
    function offsetGet($offset)
    {
        $tmp = $this->offset_parse($offset);

        if ($tmp['namespace'])
        {
            return $this->namespace_get($tmp['namespace'], $tmp['key']);
        }
        else
        {
            return $this->get($tmp['key']);
        }
    }
    
    function offsetSet($offset, $value)
    {
        $tmp = $this->offset_parse($offset);
        
        if ($tmp['namespace'])
        {
            $this->namespace_set($tmp['namespace'], $tmp['key'], $value, $tmp['timeout'], $tmp['flag']);
        }
        else
        {
            $this->set($tmp['key'], $value, $tmp['timeout'], $tmp['flag']);
        }
    }
    
    function offsetUnset($offset)
    {
        $tmp = $this->offset_parse($offset);

        if ($tmp['namespace'])
        {
            $this->namespace_delete($tmp['namespace'], $tmp['key'], $tmp['timeout']);
        }
        else
        {
            $this->delete($tmp['key'], $tmp['timeout']);
        }
    }
    
    //offset 解析
    private function offset_parse($offset)
    {
        $tmp = explode(':', $offset);
        $result = array('namespace'=>null, 'key'=>null, 'timeout'=>null, 'flag'=>null);
        if (isset($tmp[1]) && $tmp[1] === '')
        {
            $result['namespace'] = $tmp[0];
            $tmp = array_slice($tmp, 2);
        }

        $result['key'] = $tmp[0];
        $result['timeout'] = isset($tmp[1]) ? intval($tmp[1]) : null;
        $result['flag'] = isset($tmp[2]) ? ($tmp[2] ? 0 : 1) : null;
        
        return $result;
    }
    
    //默认值设置
    function set_default_timeout($val)
    {
        $this->default_timeout = $val;
    }
    
    function get_default_timeout()
    {
        return $this->default_timeout;
    }
    
    function set_default_flag($val)
    {
        $this->default_flag = empty($val) ? 0 : 1;
    }
    
    function get_default_flag()
    {
        return $this->default_flag;
    }
    
    function set_namespace_timeout($namespace, $val)
    {
        $this->namespace_timeout[$namespace] = $val;
    }
    
    function get_namespace_timeout($namesapce)
    {
        return isset($this->namespace_timeout[$namesapce]) ? $this->namespace_timeout[$namesapce] : $this->default_timeout;
    }
    
    function set_namespace_flag($namespace, $val)
    {
        $val = empty($val) ? 0 : 1;
        $this->namespace_flag[$namespace] = $val;
    }
    
    function get_namespace_flag($namespace)
    {
        return isset($this->namespace_flag[$namespace]) ? $this->namespace_flag[$namespace] : $this->default_flag;
    }
    
    
}
?>