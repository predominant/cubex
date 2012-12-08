<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 12:11
 * Description:
 */

namespace Cubex\Base;

class Callback
{

  const TYPE_GENERIC   = 'generic';
  const TYPE_FILTER    = 'filter';
  const TYPE_VALIDATOR = 'validator';

  private $_method;
  private $_options;
  private $_type;

  public function __construct($method, $options = array(), $callback_type = null)
  {
    $this->_method  = $method;
    $this->_options = $options;
    $this->_type    = $callback_type;
  }

  public static function _($method, $options = array(), $callback_type = null)
  {
    return new callback($method, $options, $callback_type);
  }

  public static function validator($method, $options = array())
  {
    return new callback($method, $options, self::TYPE_VALIDATOR);
  }

  public static function filter($method, $options = array())
  {
    return new callback($method, $options, self::TYPE_FILTER);
  }

  public function Process($input = null)
  {
    if($this->_type == self::TYPE_FILTER && \is_string($this->_method))
    {
      if(!\function_exists($this->_method) && \method_exists("\\Cubex\\Data\\Filter", $this->_method))
      {
        $this->_method = array("\\Cubex\\Data\\Filter", $this->_method);
      }
    }

    if($this->_type == self::TYPE_VALIDATOR && \is_string($this->_method))
    {
      if(!\function_exists($this->_method) && \method_exists("\\Cubex\\Data\\Validate", $this->_method))
      {
        $this->_method = array("\\Cubex\\Data\\Validate", $this->_method);
      }
    }

    return \call_user_func_array($this->_method, \array_merge(array($input), $this->_options));
  }
}
