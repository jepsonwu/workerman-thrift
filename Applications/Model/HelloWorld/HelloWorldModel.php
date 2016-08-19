<?php
namespace Application\Model\HelloWorld;

use Application\Model\CommonModel;

/**
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/19
 * Time: 上午10:21
 */
class HelloWorldModel extends CommonModel
{
    //定义连接数据库 默认mysql
    //protected $_database_type = 'mongo';

    //定义配置键值
    protected $_config_name = 'mysql';

    //保持持久连接 默认true
    //protected $_keepalive = false;

    //定义表名
    protected $_tablename = 'hello_world';

    //定义主键
    protected $_primary = 'id';

    public function getName()
    {
        $this->where("id", 1);
        return $this->getValue('name');
    }
}