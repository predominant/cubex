<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 10:57
 * Description:
 */

namespace Cubex\Data;

use Cubex\Base\Callback;

abstract class Model implements \IteratorAggregate
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
  private $_invalid_attributes;
  //TODO: on load, store data results in ephemeral to stop re-collecting data from source
  protected static $_ephemeral_datastore;

  /*
   * Automatically add all public properties as attributes and unset them for automatic handling of data
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

  public function __toString()
  {
    $properties = array();
    foreach($this->_attributes as $attr)
    {
      if($attr instanceof Attribute)
      {
        if(!$attr->isEmpty())
        {
          $properties[] = $attr->getName() . ' = ' .
          (is_scalar($attr->data()) ? $attr->data() : print_r($attr->data(), true));
        }
      }
    }

    return \get_class($this) . " {" . implode(', ', $properties) . "}";
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

  public function __get($name)
  {
    return $this->call("get" . \ucwords($name), null);
  }

  public function __set($name, $value)
  {
    return $this->call("set" . \ucwords($name), array($value));
  }

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

  public function getID()
  {
    if($this->isCompositeID())
    {
      return $this->getCompositeID();
    }
    else
    {
      return $this->attribute($this->getIDKey())->rawData();
    }
  }

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

  protected function getCompositeID()
  {
    $result = array();
    foreach($this->getCompositeKeys() as $key)
    {
      $result[] = $this->attribute($key)->rawData();
    }

    return implode('|', $result);
  }

  protected function getCompositeKeys()
  {
    return array();
  }

  public function composeID( /*$key1,$key2*/)
  {
    return \implode("|", \func_get_args());
  }

  /*
   * @returns Cubex\Data\Connection
   */
  abstract protected function dataConnection();

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

  final protected function addAttribute(Attribute $attribute)
  {
    $this->_attributes[strtolower($attribute->getName())] = $attribute;
  }

  final protected function attributeExists($attribute)
  {
    return isset($this->_attributes[$attribute]);
  }

  final protected function addAttributeFilter($attribute, Callback $filter)
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

  final protected function addAttributeValidator($attribute, Callback $filter)
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

  final protected function addAttributeOption($attribute, $option)
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
          unset($this->_invalid_attributes[$attribute]);
          if(!$attr->valid($process_all_validators))
          {
            $valid                                 = false;
            $this->_invalid_attributes[$attribute] = $attr->errors();
            if($fail_first) return false;
          }
        }
      }
    }

    return $valid;
  }


  public function saveChanges()
  {
    return false;
  }

  public function delete()
  {
    return false;
  }

  public function reload()
  {
    $this->clearEphemeral($this->getID());

    return $this->load($this->getID());
  }

  public function load($id, $columns = array("*"))
  {
    if(\is_array($id))
    {
      $id = $this->composeID($id);
    }

    //Load single model
    return false;
  }

  public function loadComposite($columns = array("*") /*,$key1,$key2*/)
  {
    $args = \func_get_args();
    \array_shift($args);

    return $this->load(array($args), $columns);
  }

  public function loadAll($columns = array("*"))
  {
    //Load array of models
    return array();
  }

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

  public function loadFromStdClass(\stdClass $data)
  {
    $this->loadFromArray((array)$data);

    return $this;
  }

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

  public static function All($columns = array('*'))
  {
    $user = new static;

    return $user->loadAll($columns);
  }

  public function clearEphemeral($id)
  {
    unset(self::$_ephemeral_datastore[$id]);

    return true;
  }

  public function addEphemeral($id, $data)
  {
    self::$_ephemeral_datastore[$id] = $data;

    return $this;
  }

  public function getEphemeral($id)
  {
    return self::$_ephemeral_datastore[$id];
  }

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
