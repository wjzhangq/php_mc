php memecache 类的扩展，支持数组操作和名称空间， 将原来set，get 方法中flag 和 timeout参数的顺序交换了一下。

1. mc 初始化
$mc = new mc();
$mc->connect('127.0.0.1');

2 . 设置默认的过期时间和flag， 可以为每个名称空间设置这两个参数，如果不指定就按默认的值。
$mc->set_default_timeout(60); //默认过期时间为60s
$mc->set_namespace_timeout('user', 120); //将user名称空间下的的数据过期的默认值为120s


3. 存储一个数据： 
$mc['key'] = 'value';
$mc['key:10'] = 'value'; //主键为'key', value 值有10s过期时间
$mc['key:20:1'] = 'value'; //主键为'key', value 值有20s过期时间, 启用zip压缩数据
$mc['user::key'] = 'value'; //名称空间为user, 主键为'key', value 值有10s过期时间

4. 获取数据
echo $mc['key'];
echo $mc['user::key']; //有名称空间的
echo $mc['user::']; //非法操作

5. 删除数据
unset($mc['key']);
unset($mc['user::key']);
unset($mc['user::']); //删除名称空间下所有数据
