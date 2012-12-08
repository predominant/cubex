<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 13:01
 * Description:
 */

namespace Cubex\Data;

class Filter
{
  public static function singleSpaces($string)
  {
    return \preg_replace('!\s+!', ' ', $string);
  }

  public static function email($email)
  {
    return \strtolower(trim($email));
  }

  public static function trim($string, $charlist = null)
  {
    return \trim($string, $charlist);
  }

  public static function leftTrim($string, $charlist = null)
  {
    return \ltrim($string, $charlist);
  }

  public static function rightTrim($string, $charlist = null)
  {
    return \rtrim($string, $charlist);
  }

  public static function lower($string)
  {
    return \strtolower($string);
  }

  public static function upper($string)
  {
    return \strtoupper($string);
  }

  public static function upperWords($string)
  {
    return \ucwords(\strtolower($string));
  }

  public static function clean($string)
  {
    return \trim(\strip_tags($string));
  }

  public static function boolean($string)
  {
    return \in_array($string, array('true', '1', 1, true), true);
  }

  public static function int($string)
  {
    return \intval($string);
  }

  public static function float($string)
  {
    return \floatval($string);
  }

  public static function arr($string)
  {
    if(\is_array($string)) return $string;
    if(\is_object($string)) return (array)$string;
    if(\stristr($string, ',')) return \explode(',', $string);
    else return array($string);
  }

  /**
   * Returns a name object
   * @param $full_name
   * @return \stdClass
   */
  public static function splitName($full_name)
  {
    $full_name = \preg_replace('!\s+!', ' ', $full_name); // Make multiple spaces single
    $name             = new \stdClass();
    $parts            = \explode(' ', \trim($full_name));
    $name->first_name = $name->middle_name = $name->last_name = '';
    switch(\count($parts))
    {
      case 1:
        $name->first_name = $parts[0];
        break;
      case 2:
        $name->first_name = $parts[0];
        $name->last_name  = $parts[1];
        break;
      default:
        $name->first_name  = \array_shift($parts);
        $name->last_name   = \array_pop($parts);
        $name->middle_name = \implode(' ', $parts);
        break;
    }

    return $name;
  }
}
