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

  public function Get($key);

  public function Multi(array $keys);

  public function Set($key, $data, $expire=0);

}
