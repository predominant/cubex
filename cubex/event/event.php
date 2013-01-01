<?php
/**
 * User: Brooke Bryan
 * Date: 01/01/13
 * Time: 19:29
 * Description: Standard Event
 */
namespace Cubex\Event;

class Event implements EventInterface
{
  private $_name;
  private $_args;
  private $_callee;

  public function __construct($name, $args = [], $callee = null)
  {
    $this->_name   = $name;
    $this->_args   = $args;
    $this->_callee = $callee;
  }

  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function setCallee($callee)
  {
    $this->_callee = $callee;
    return $this;
  }

  public function getCallee()
  {
    return $this->_callee;
  }

  public function getParams()
  {
    return $this->_args;
  }

  public function getParam($name, $default = null)
  {
    return isset($this->_args[$name]) ? $this->_args[$name] : $default;
  }

  public function setParams($params)
  {
    $this->_args = $params;
    return $this;
  }

  public function setParam($name, $value)
  {
    $this->_args[$name] = $value;
    return $this;
  }
}
