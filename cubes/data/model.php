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

  const CONFIG_IDS = 'id-mechanism';

  const ID_AUTOINCREMENT = 'auto';
  const ID_MANUAL        = 'manual';

  private $_attributes;
  private $_invalid_attributes;

  /*
   * Automatically add all public properties as attributes and unset them for automatic handling of data
   */
  public function __construct()
  {
    $class = new \ReflectionClass(get_class($this));
    foreach($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $p)
    {
      $property = $p->getName();
      if(!$this->attributeExists($property)) $this->addAttribute(new Attribute($property));
      unset($this->$property);
    }
  }

  /*
   * Column Name for ID field
   * @return string Name of ID column
   */
  public function getIDKey()
  {
    return 'id';
  }

  /*
   * @param string Mode, either 'r' (reading) or 'w' (reading and writing)
   * @returns DataConnection
   */
  abstract protected function dataConnection($mode);

  protected function getConfiguration()
  {
    return array(
      self::CONFIG_IDS => self::ID_AUTOINCREMENT
    );
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

  final protected function attributeExists($attribute)
  {
    return isset($this->_attributes[$attribute]);
  }

  final protected function addAttributeFilter($attribute,\Cubex\Base\Callback $filter)
  {
    if(!isset($this->_attributes[$attribute])) return false;
    $attr = $this->_attributes[$attribute];
    if($attr instanceof Attribute)
    {
      $this->_attributes[$attribute] = $attr->addFilter($filter);
      return true;
    }
    return false;
  }

  final protected function addAttributeValidator($attribute,\Cubex\Base\Callback $filter)
  {
    if(!isset($this->_attributes[$attribute])) return false;
    $attr = $this->_attributes[$attribute];
    if($attr instanceof Attribute)
    {
      $this->_attributes[$attribute] = $attr->addValidator($filter);
      return true;
    }
    return false;
  }
  final protected function addAttributeOption($attribute,$option)
  {
    if(!isset($this->_attributes[$attribute])) return false;
    $attr = $this->_attributes[$attribute];
    if($attr instanceof Attribute)
    {
      $this->_attributes[$attribute] = $attr->addOption($option);
      return true;
    }
    return false;
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
          $this->attribute($attribute)->setData($args[0]);

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

  public function __set($name, $value)
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
          unset($this->_invalid_attributes[$attribute]);
          if(!$attr->valid($process_all_validators))
          {
            $valid                       = false;
            $this->_invalid_attributes[$attribute] = $attr->errors();
            if($fail_first) return false;
          }
        }
      }
    }

    return $valid;
  }
}
