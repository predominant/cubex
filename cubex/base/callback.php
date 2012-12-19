<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 12:11
 * Description:
 */

namespace Cubex\Base;

/**
 * Standard callback handler
 */
class Callback
{

  /**
   * Generic callback type
   */
  const TYPE_GENERIC = 'generic';
  /**
   * Callback to a filter
   */
  const TYPE_FILTER = 'filter';
  /**
   * Callback to a validator
   */
  const TYPE_VALIDATOR = 'validator';

  private $_method;
  private $_options;
  private $_type;

  /**
   * @param        $method
   * @param array  $options
   * @param string $callbackType
   */
  public function __construct($method, $options = array(), $callbackType = self::TYPE_GENERIC)
  {
    $this->_method  = $method;
    $this->_options = $options;
    $this->_type    = $callbackType;
  }

  /**
   * create a new callback statically
   *
   * @param        $method
   * @param array  $options
   * @param string $callbackType
   *
   * @return Callback
   */
  public static function _($method, $options = array(), $callbackType = self::TYPE_GENERIC)
  {
    return new callback($method, $options, $callbackType);
  }

  /**
   * Create a new validator callback
   *
   * @param       $method
   * @param array $options
   *
   * @return Callback
   */
  public static function validator($method, $options = array())
  {
    return new callback($method, $options, self::TYPE_VALIDATOR);
  }

  /**
   * Create a new filter callback
   *
   * @param       $method
   * @param array $options
   *
   * @return Callback
   */
  public static function filter($method, $options = array())
  {
    return new callback($method, $options, self::TYPE_FILTER);
  }

  /**
   * Process a callback with input
   *
   * @param null $input
   *
   * @return mixed
   */
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
