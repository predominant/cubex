<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 10:57
 * Description:
 */

namespace Cubex\Data;

use Cubex\Base\Callback;

/**
 * Base Model
 */
abstract class Model implements \IteratorAggregate, \JsonSerializable
{

  const CONFIG_IDS = 'id-mechanism';

  /**
   * Auto Incrementing ID
   */
  const ID_AUTOINCREMENT = 'auto';
  /**
   * Manual ID Assignment
   */
  const ID_MANUAL = 'manual';
  /**
   * Combine multiple keys to a single key for store
   */
  const ID_COMPOSITE = 'composite';
  /**
   * Base ID on multiple keys
   */
  const ID_COMPOSITE_SPLIT = 'compositesplit';

  private $_attributes;
  private $_invalidAttributes;
  //TODO: on load, store data results in ephemeral to stop re-collecting data from source
  protected static $_ephemeralDatastore;

  /*
   * Automatically add all public properties as attributes and unset them for automatic handling of data
   */
  /**
   *
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
  public function __clone()
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
    $attrs = array();
    foreach($this->_attributes as $attr)
    {
      if($attr instanceof Attribute)
      {
        if(!$attr->isEmpty())
        {
          $attrs[$attr->getName()] = $attr->data();
        }
      }
    }

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
   * @return mixed
   */
  public function getTableName()
  {
    return \str_replace(
      '_models', '', \str_replace('cubex_modules_', '', \strtolower(\str_replace('\\', '_', \get_class($this))))
    );
  }

  /**
   * Column Name for ID field
   *
   * @return string Name of ID column
   */
  public function getIDKey()
  {
    return 'id';
  }

  /**
   * @return string
   */
  public function getID()
  {
    if($this->isCompositeID())
    {
      return $this->getCompositeID();
    }
    else
    {
      $attr = $this->attribute($this->getIDKey());
      if($attr !== null)
      {
        return $attr->rawData();
      }
      else
      {
        return null;
      }
    }
  }

  /**
   * @return bool
   */
  public function isCompositeID()
  {
    $config = $this->getConfiguration();
    if(isset($config[self::CONFIG_IDS]))
    {
      if(\in_array($config[self::CONFIG_IDS], array(self::ID_COMPOSITE, self::ID_COMPOSITE_SPLIT)))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * @return string
   */
  protected function getCompositeID()
  {
    $result = array();
    foreach($this->getCompositeKeys() as $key)
    {
      $result[] = $this->attribute($key)->rawData();
    }

    return implode('|', $result);
  }

  /**
   * @return array
   */
  protected function getCompositeKeys()
  {
    return array();
  }

  /**
   * @return string
   */
  public function composeID( /*$key1,$key2*/)
  {
    return \implode("|", \func_get_args());
  }

  /*
   * @returns Cubex\Data\Connection
   */
  /**
   * @return mixed
   */
  abstract protected function dataConnection();

  /**
   * @return array
   */
  protected function getConfiguration()
  {
    return array(
      self::CONFIG_IDS => self::ID_AUTOINCREMENT
    );
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
      return false;
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
      return false;
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
      return false;
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
   * @param bool $process_all_validators
   * @param bool $fail_first
   *
   * @return bool
   */
  public function isValid($attributes = null, $process_all_validators = false, $fail_first = false)
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
          if(!$attr->valid($process_all_validators))
          {
            $valid                                = false;
            $this->_invalidAttributes[$attribute] = $attr->errors();
            if($fail_first)
              return false;
          }
        }
      }
    }

    return $valid;
  }


  /**
   * @return bool
   */
  public function saveChanges()
  {
    return false;
  }

  /**
   * @return bool
   */
  public function delete()
  {
    return false;
  }

  /**
   * @return bool
   */
  public function reload()
  {
    $this->clearEphemeral($this->getID());

    return $this->load($this->getID());
  }

  /**
   * @param       $id
   * @param array $columns
   *
   * @return bool
   */
  public function load($id, $columns = array("*"))
  {
    if(\is_array($id))
    {
      $id = $this->composeID($id);
    }

    //Load single model
    return false;
  }

  /**
   * @param array $columns
   *
   * @return bool
   */
  public function loadComposite($columns = array("*") /*,$key1,$key2*/)
  {
    $args = \func_get_args();
    \array_shift($args);

    return $this->load(array($args), $columns);
  }

  /**
   * @param array $columns
   *
   * @return array
   */
  public function loadAll($columns = array("*"))
  {
    //Load array of models
    return array();
  }

  /**
   * @param array $data
   *
   * @return Model
   */
  public function loadFromArray(array $data)
  {
    foreach($data as $k => $v)
    {
      if($this->attributeExists($k))
      {
        $set = "set$k";
        $this->$set($this->attribute($k)->unserialize($v));
      }
    }
    $this->unmodifyAttributes();

    return $this;
  }

  /**
   * @param \stdClass $data
   *
   * @return Model
   */
  public function loadFromStdClass(\stdClass $data)
  {
    $this->loadFromArray((array)$data);

    return $this;
  }

  /**
   * @param array $rows
   *
   * @return array
   */
  public function loadMultiFromArray(array $rows)
  {
    $result = array();
    $id     = $this->getIDKey();
    foreach($rows as $row)
    {
      $object = clone $this;
      if(\is_object($row))
      {
        if($id && isset($row->$id))
        {
          $result[$row->$id] = $object->loadFromStdClass($row);
        }
      }
      else if(\is_array($row))
      {
        if($id && isset($row[$id]))
        {
          $result[$row[$id]] = $object->loadFromArray($row);
        }
      }
    }

    return $result;
  }

  /**
   * @param array $columns
   *
   * @return array
   */
  public static function All($columns = array('*'))
  {
    $user = new static;

    return $user->loadAll($columns);
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
