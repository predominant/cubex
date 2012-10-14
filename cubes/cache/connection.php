<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 01:03
 * Description:
 */

namespace Cache;

interface Connection
{

  public function __construct(array $configuration);

  public function get($key);

  public function multi(array $keys);

  public function set($key, $data, $expire=0);

}
