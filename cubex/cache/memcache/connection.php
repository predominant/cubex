<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 00:57
 * Description:
 */
namespace Cubex\Cache\Memcache;

use Cubex\Data\Handler;

/**
 * Memcache connections
 */
class Connection implements \Cubex\Cache\Connection
{
  private $_connection = null;

  /**
   * Create new memcache connection
   *
   * @param \Cubex\Data\Handler $config
   */
  public function __construct(Handler $config)
  {
    $this->_connection = new \Memcache();
    $this->_connection->addserver($config->getStr("hostname"));
  }

  /**
   * @param string $mode Either 'r' (reading) or 'w' (reading and writing)
   */
  public function connect($mode = 'w')
  {
    // TODO: Implement connect() method.
  }

  /**
   * Disconnect from the connection
   *
   * @return mixed
   */
  public function disconnect()
  {
    // TODO: Implement disconnect() method.
  }

  /**
   * Escape column name
   *
   * @param $column
   * @return mixed
   */
  public function escapeColumnName($column)
  {
    // TODO: Implement escapeColumnName() method.
  }

  /**
   * Escape string value for insert
   *
   * @param $string
   * @return mixed
   */
  public function escapeString($string)
  {
    // TODO: Implement escapeString() method.
  }

  /**
   * Get data by key
   *
   * @param $key
   * @return mixed
   */
  public function get($key)
  {
    // TODO: Implement get() method.
  }

  /**
   * Get data by multiple keys
   *
   * @param array $keys
   *
   * @return mixed
   */
  public function multi(array $keys)
  {
    // TODO: Implement multi() method.
  }

  /**
   * Cache data out to a key, with expiry time in seconds
   *
   * @param     $key
   * @param     $data
   * @param int $expire
   *
   * @return mixed
   */
  public function set($key, $data, $expire = 0)
  {
    // TODO: Implement set() method.
  }
}
