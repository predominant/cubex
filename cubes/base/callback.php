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

  public function Process($input = null)
  {
    if($this->_type == 'filter' && is_string($this->_method))
    {
      if(!function_exists($this->_method) && method_exists("Filter", $this->_method))
      {
        $this->_method = array("Filter", $this->_method);
      }
    }

    if($this->_type == 'validator' && is_string($this->_method))
    {
      if(!function_exists($this->_method) && method_exists("Validate", $this->_method))
      {
        $this->_method = array("Validate", $this->_method);
      }
    }

    return call_user_func_array($this->_method, array_merge(array($input), $this->_options));
  }
}
