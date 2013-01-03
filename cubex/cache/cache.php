<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 01:03
 * Description:
 */

namespace Cubex\Cache;

/**
 * Base caching connection
 */
use Cubex\Data\Connection;

interface Cache extends Connection
{
  /**
   * Get data by key
   *
   * @param $key
   *
   * @return mixed
   */
  public function get($key);

  /**
   * Get data by multiple keys
   *
   * @param array $keys
   *
   * @return mixed
   */
  public function multi(array $keys);

  /**
   * Cache data out to a key, with expiry time in seconds
   *
   * @param     $key
   * @param     $data
   * @param int $expire
   *
   * @return mixed
   */
  public function set($key, $data, $expire = 0);
}
