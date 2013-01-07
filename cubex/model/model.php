<?php
/**
 * User: brooke.bryan
 * Date: 07/01/13
 * Time: 18:13
 * Description:
 */

namespace Cubex\Model;

use Cubex\Base\Callback;
use Cubex\Data\Attribute;

abstract class Model
{
  protected $_attributes;
  protected $_invalidAttributes;

  //TODO: on load, store data results in ephemeral to stop re-collecting data from source
  protected static $_ephemeralDatastore;

  /**
   * Automatically add all public properties as attributes
   * and unset them for automatic handling of data
   */
  public function __construct()
  {
    $class = new \ReflectionClass(get_class($this));
    foreach($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $p)
    {
      $property = $p->getName();
      if(!$this->attributeExists($property))
      {
        $this->addAttribute(new Attribute($property, false, null, null, null, $this->$property));
      }
      unset($this->$property);
    }
  }

  /**
   *
   */
  protected function __clone()
  {
    $attrs             = $this->_attributes;
    $this->_attributes = array();
    foreach($attrs as $attr)
    {
      if($attr instanceof Attribute)
      {
        $attr->setData(null);
        $this->addAttribute(clone $attr);
      }
    }
  }

  /**
   * @return \ArrayIterator
   */
  public function getIterator()
  {
    $attrs = $this->_getRawAttributesArr($this->_attributes);

    return new \ArrayIterator($attrs);
  }

  /**
   * @return string
   */
  public function __toString()
  {
    $properties = array();
    $attributes = $this->_getRawAttributesArr($this->_attributes);
    foreach($attributes as $name => $value)
    {
      $property = "$name = ";
      $property .= is_scalar($value) ? $value : print_r($value, true);
      $properties[] = $property;
    }

    return \get_class($this) . " {" . implode(', ', $properties) . "}";
  }

  /**
   * @return array|mixed
   */
  public function jsonSerialize()
  {
    return $this->_getRawAttributesArr($this->_attributes);
  }

  /**
   * @param array $attributes
   *
   * @return array
   */
  protected function _getRawAttributesArr(array $attributes)
  {
    $rawAttributes = [];

    foreach($attributes as $attribute)
    {
      if($attribute instanceof Attribute)
      {
        $rawAttributes[$attribute->getName()] = $attribute->data();
      }
    }

    return $rawAttributes;
  }

  /**
   * @param $method
   * @param $args
   *
   * @return bool|Model|mixed
   */
  public function __call($method, $args)
  {
    // NOTE: PHP has a bug that static variables defined in __call() are shared
    // across all children classes. Call a different method to work around this
    // bug.
    return $this->call($method, $args);
  }

  /**
   * @param $method
   * @param $args
   *
   * @return bool|Model|mixed
   * @throws \Exception
   */
  final protected function call($method, $args)
  {
    switch(\substr($method, 0, 3))
    {
      case 'set':
        $attribute = \strtolower(substr($method, 3));
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
        $attribute = \strtolower(substr($method, 3));
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

  /**
   * @param $name
   *
   * @return bool|Model|mixed
   */
  public function __get($name)
  {
    return $this->call("get" . \ucwords($name), null);
  }

  /**
   * @param $name
   * @param $value
   *
   * @return bool|Model|mixed
   */
  public function __set($name, $value)
  {
    return $this->call("set" . \ucwords($name), array($value));
  }


  /**
   * @return array
   */
  public function getConfiguration()
  {
    return array();
  }


  /**
   * @param $name
   *
   * @return Attribute
   */
  final protected function attribute($name)
  {
    return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
  }

  /**
   * @param Attribute $attribute
   */
  final protected function addAttribute(Attribute $attribute)
  {
    $this->_attributes[strtolower($attribute->getName())] = $attribute;
  }

  /**
   * @param $attribute
   *
   * @return bool
   */
  final protected function attributeExists($attribute)
  {
    return isset($this->_attributes[$attribute]);
  }

  /**
   * @param                      $attribute
   * @param \Cubex\Base\Callback $filter
   *
   * @return bool
   */
  final protected function addAttributeFilter($attribute, Callback $filter)
  {
    if(!isset($this->_attributes[$attribute]))
    {
      return false;
    }
    $attr = $this->_attributes[$attribute];
    if($attr instanceof Attribute)
    {
      $this->_attributes[$attribute] = $attr->addFilter($filter);

      return true;
    }

    return false;
  }

  /**
   * @param                      $attribute
   * @param \Cubex\Base\Callback $filter
   *
   * @return bool
   */
  final protected function addAttributeValidator($attribute, Callback $filter)
  {
    if(!isset($this->_attributes[$attribute]))
    {
      return false;
    }
    $attr = $this->_attributes[$attribute];
    if($attr instanceof Attribute)
    {
      $this->_attributes[$attribute] = $attr->addValidator($filter);

      return true;
    }

    return false;
  }

  /**
   * @param $attribute
   * @param $option
   *
   * @return bool
   */
  final protected function addAttributeOption($attribute, $option)
  {
    if(!isset($this->_attributes[$attribute]))
    {
      return false;
    }
    $attr = $this->_attributes[$attribute];
    if($attr instanceof Attribute)
    {
      $this->_attributes[$attribute] = $attr->addOption($option);

      return true;
    }

    return false;
  }

  /**
   * @param null $attributes
   * @param bool $processAllValidators
   * @param bool $failFirst
   *
   * @return bool
   */
  public function isValid($attributes = null, $processAllValidators = false, $failFirst = false)
  {
    $valid = true;
    if($attributes === null)
    {
      $attributes = \array_keys($this->_attributes);
    }

    if(\is_array($attributes))
    {
      foreach($attributes as $attribute)
      {
        $attr = isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : null;
        if($attr instanceof Attribute)
        {
          unset($this->_invalidAttributes[$attribute]);
          if(!$attr->valid($processAllValidators))
          {
            $valid                                = false;
            $this->_invalidAttributes[$attribute] = $attr->errors();
            if($failFirst)
            {
              return false;
            }
          }
        }
      }
    }

    return $valid;
  }


  /**
   * @param $id
   *
   * @return bool
   */
  public function clearEphemeral($id)
  {
    unset(self::$_ephemeralDatastore[$id]);

    return true;
  }

  /**
   * @param $id
   * @param $data
   *
   * @return Model
   */
  public function addEphemeral($id, $data)
  {
    self::$_ephemeralDatastore[$id] = $data;

    return $this;
  }

  /**
   * @param $id
   *
   * @return mixed
   */
  public function getEphemeral($id)
  {
    return self::$_ephemeralDatastore[$id];
  }

  /**
   *
   */
  protected function unmodifyAttributes()
  {
    foreach($this->_attributes as $attr)
    {
      if($attr instanceof Attribute)
      {
        $attr->unsetModified();
      }
    }
  }

  /**
   * @return array
   */
  public function getModifiedAttributes()
  {
    $modified = array();
    foreach($this->_attributes as $attr)
    {
      if($attr instanceof Attribute)
      {
        if($attr->isModified())
        {
          $modified[] = $attr;
        }
      }
    }

    return $modified;
  }

  /**
   * @param null $name
   *
   * @return Model
   */
  public function revert($name = null)
  {
    if($name !== null)
    {
      $this->attribute($name)->revert();
    }
    else
    {
      foreach($this->_attributes as $attr)
      {
        if($attr instanceof Attribute)
        {
          $attr->revert();
        }
      }
    }

    return $this;
  }
}
