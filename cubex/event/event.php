<?php
/**
 * User: Brooke Bryan
 * Date: 01/01/13
 * Time: 19:29
 * Description: Standard Event
 */
namespace Cubex\Event;

use Cubex\Traits\Data\Handler;

class Event implements EventInterface
{
  use Handler;

  private $_name;
  private $_callee;

  public function __construct($name, $args = array(), $callee = null)
  {
    $this->_name   = $name;
    $this->_data   = $args;
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
    return $this->_data;
  }

  public function getParam($name, $default = null)
  {
    return isset($this->_data[$name]) ? $this->_data[$name] : $default;
  }

  public function setParams($params)
  {
    $this->_data = $params;
    return $this;
  }

  public function setParam($name, $value)
  {
    $this->_data[$name] = $value;
    return $this;
  }
}
