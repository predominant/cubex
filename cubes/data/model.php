<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 10:57
 * Description:
 */

namespace Cubex\Data;

abstract class Model
{

  private $_attributes;
  private $_invalid_attributes;

  public function __construct()
  {
    $this->unsetAttributePublics();
  }

  /**
   * @param $name
   * @return Attribute
   */
  final protected function attribute($name)
  {
    return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
  }

  final protected function addAttribute(Attribute $attribute)
  {
    $this->_attributes[strtolower($attribute->getName())] = $attribute;
  }

  final protected function unsetAttributePublics()
  {
    foreach((array)array_keys($this->_attributes) as $key)
    {
      unset($this->$key);
    }
    return true;
  }

  protected function attributeExists($attribute)
  {
    return isset($this->_attributes[$attribute]);
  }

  public function __call($method, $args)
  {
    // NOTE: PHP has a bug that static variables defined in __call() are shared
    // across all children classes. Call a different method to work around this
    // bug.
    return $this->call($method, $args);
  }

  final protected function call($method, $args)
  {
    switch(substr($method, 0, 3))
    {
      case 'set':
        $attribute = strtolower(substr($method, 3));
        if($this->attributeExists($attribute))
        {
          $this->attribute($attribute)->setData("Defined " . $args[0]);

          return $this;
        }
        else
        {
          throw new \Exception("Invalid Attribute " . $attribute);
        }
        break;
      case 'get':
        $attribute = strtolower(substr($method, 3));
        if($this->attributeExists($attribute))
        {
          return $this->attribute($attribute)->data();
        }
        else
        {
          throw new \Exception("Invalid Attribute " . $attribute);
        }
        break;
    }
    return true;
  }

  public function __get($name)
  {
    return $this->call("get" . ucwords($name), null);
  }

  public function __set($name,$value)
  {
    return $this->call("set" . ucwords($name), array($value));
  }

  public function valid($attributes = null, $process_all_validators = false, $fail_first = false)
  {
    $valid = true;
    if($attributes === null)
    {
      $attributes = array_keys($this->_attributes);
    }

    if(is_array($attributes))
    {
      foreach($attributes as $attribute)
      {
        $attr = isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : null;
        if($attr instanceof Attribute)
        {
          if(!$attr->valid($process_all_validators))
          {
            $valid                       = false;
            $this->_invalid_attributes[] = $attribute;
            if($fail_first) return false;
          }
        }
      }
    }

    return $valid;
  }
}
