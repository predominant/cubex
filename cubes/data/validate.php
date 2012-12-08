<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 13:01
 * Description:
 */

namespace Cubex\Data;

class Validate
{

  public static function email($email)
  {
    if(!\filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \Exception('Invalid Email Address');

    return true;
  }

  public static function length($string, $min = 1, $max = null)
  {
    if($min && $min > 0 && \strlen($string) <= $min) throw new \Exception("Minimum Length of $min Required");
    if($max && $max > 0 && \strlen($string) >= $max) throw new \Exception("Maximum Length of $max Required");

    return true;
  }

  public static function notEmpty($string)
  {
    if(empty($string)) throw new \Exception("Input Empty");

    return true;
  }

  public static function time($time)
  {
    if(\is_int($time) && $time > 0) return true;
    if(\strtotime($time) > 0) return true;
    throw new \Exception('Invalid time format');
  }

  public static function date($date)
  {
    //convert string to time stamp and back to date again
    $timestamp = \strtotime($date);
    if(\date('Y-m-d', $timestamp) == $date) return true;
    throw new \Exception('Invalid date format');
  }

  public static function int($input)
  {
    if(\is_int($input)) return true;
    if(\strlen(intval($input)) == strlen($input)) return true;

    throw new \Exception('Invalid Integer');
  }

  public static function float($input)
  {
    if(\is_float($input)) return true;
    if(\floatval($input) == $input) return true;

    throw new \Exception('Invalid Float');
  }

  public static function bool($input)
  {
    if(\in_array($input, array('true', '1', 1, true, 'false', '0', 0, false), true)) return true;

    throw new \Exception('Invalid Boolean');

  }

  public static function scalar($input)
  {
    if(\is_scalar($input)) return true;
    else throw new \Exception("Invalid Scalar");
  }

  public static function timestamp($input)
  {
    if((string)(int)$input === (string)$input && ($input <= PHP_INT_MAX) && ($input >= ~PHP_INT_MAX)) return true;
    throw new \Exception("Invalid Unix Timestamp");
  }

  public static function percent($input)
  {
    if(\is_int($input) && $input >= 0 && $input <= 100) return true;

    throw new \Exception('Invalid Percentage');
  }

  public static function arr($input, $array_type = "array")
  {
    if(\is_object($input)) $input = (array)$input;
    if(!\is_array($input)) throw new \Exception('Invalid Array');

    switch($array_type)
    {
      case "strings":
        foreach($input as $check)
        {
          if(\gettype($check) != "string")
          {
            throw new \Exception('Invalid array of strings');
          }
        }

        return true;
        break;
      case "ints":
        foreach($input as $check)
        {
          if(\gettype($check) != "integer")
          {
            throw new \Exception('Invalid array of strings');
          }
        }

        return true;
        break;
      case "objects":
        foreach($input as $check)
        {
          if(\gettype($check) != "object")
          {
            throw new \Exception('Invalid array of objects');
          }
        }

        return true;
        break;
    }

    return true;
  }

  public static function regex($input, $regex)
  {
    if(\preg_match($regex, $input)) return true;
    throw new \Exception("Input failed against " . $regex);
  }

  public static function base64($input)
  {
    if(\base64_decode($input, true) !== false) return true;
    throw new \Exception("Invalid Base64 String");
  }

  public static function url($input)
  {
    if(\filter_var($input, FILTER_VALIDATE_URL)) return true;
    throw new \Exception('Invalid URL');
  }

  public static function domain($input)
  {
    if(!\preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', $input))
      throw new \Exception('Invalid Domain');

    return true;
  }
}
