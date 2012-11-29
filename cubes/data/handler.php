<?php
/**
 * User: brooke.bryan
 * Date: 17/10/12
 * Time: 22:09
 * Description:
 */

namespace Cubex\Data;

class Handler implements \IteratorAggregate
{
  protected $_data;

  public function __construct($data = null)
  {
    if($data !== null && is_array($data))
    {
      $this->_data = $data;
    }
  }

  public function __set($name, $value)
  {
    return $this->_data[$name] = $value;
  }

  public function __get($name)
  {
    return $this->_data[$name];
  }

  public function __isset($name)
  {
    return $this->getExists($name);
  }

  public function setData($name, $value)
  {
    $this->_data[$name] = $value;
  }

  public function populate($data)
  {
    $this->_data = $data;
  }

  public function appendData(array $data)
  {
    $this->_data = array_merge($this->_data, $data);
  }

  final public function getInt($name, $default = null)
  {
    if(isset($this->_data[$name])) return (int)$this->_data[$name];
    else return $default;
  }

  final public function getFloat($name, $default = null)
  {
    if(isset($this->_data[$name])) return (float)$this->_data[$name];
    else return $default;
  }

  final public function getBool($name, $default = null)
  {
    if(isset($this->_data[$name]))
    {
      if($this->_data[$name] === 'true') return true;
      else if($this->_data[$name] === 'false') return false;
      else return (bool)$this->_data[$name];
    }
    else return $default;
  }

  final public function getStr($name, $default = null)
  {
    if(isset($this->_data[$name]))
    {
      // Normalize newlines.
      return str_replace(array("\r\n", "\r"), array("\n", "\n"), (string)$this->_data[$name]);
    }
    else return $default;
  }

  final public function getRaw($name, $default = null)
  {
    if(isset($this->_data[$name])) return $this->_data[$name];
    else return $default;
  }

  final public function getArr($name, $default = null)
  {
    if(isset($this->_data[$name]) && is_array($this->_data[$name])) return $this->_data[$name];
    else if(is_string($this->_data[$name]) && stristr($this->_data[$name], ','))
    {
      return explode(',', $this->_data[$name]);
    }
    else if(empty($this->_data[$name])) return $default;
    else if(is_scalar($this->_data[$name])) return array($this->_data[$name]);
    else if(is_object($this->_data[$name])) return (array)$this->_data[$name];
    else return $default;
  }

  final public function getObj($name, $default = null)
  {
    if(isset($this->_data[$name]))
    {
      if(is_object($this->_data[$name])) return $this->_data[$name];
      else return (object)$this->_data[$name];
    }
    else return $default;
  }

  final public function getExists($name)
  {
    return isset($this->_data[$name]);
  }

  public function getIterator()
  {
    $o = new \ArrayObject($this->_data);

    return $o->getIterator();
  }
}
