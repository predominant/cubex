<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 00:57
 * Description:
 */
namespace Cubex\Cache\Memcache;

class Connection implements \Cubex\Cache\Connection
{
  private $_connection = null;
  public function __construct(array $config)
  {
    $this->_connection = new \Memcache();
    $this->_connection->addserver($config['hostname']);
  }

  public function get($key)
  {
  }

  public function multi(array $keys)
  {

  }

  public function set($key, $data, $expire=0)
  {

  }
}
