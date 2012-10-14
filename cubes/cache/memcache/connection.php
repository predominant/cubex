<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 00:57
 * Description:
 */
namespace Cache\Memcache;

class Connection implements \Cache\Connection
{
  private $_connection = null;
  public function __construct(array $config)
  {
    $this->_connection = new \Memcache();
    $this->_connection->addserver($config['hostname']);
  }

  public function Get($key)
  {
  }

  public function Multi(array $keys)
  {

  }

  public function Set($key, $data, $expire=0)
  {

  }
}
