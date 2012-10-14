<?php
/**
 * User: Brooke Bryan
 * Date: 14/10/12
 * Time: 01:31
 * Description: Helper Methods
 */

namespace Cubex;

class C
{

  public static function ArrayValue($array, $key, $default, $default_type = 0)
  {
    if(!isset($array[$key])) return $default;
    if($array[$key] === null) return $default;
    if($array[$key] === '' && $default_type > 0) return $default;
    if($array[$key] === false && $default_type > 1) return $default;

    return $array[$key];
  }
}
