<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 10:57
 * Description:
 */

namespace Cubex\Model;

use Cubex\Base\Callback;

/**
 * Base DataModel
 */
abstract class DataModel extends Model implements \IteratorAggregate, \JsonSerializable
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

  /**
   * @return mixed
   */
  public function getTableName()
  {
    return \str_replace(
      '_models', '',
      \str_replace('cubex_modules_', '', \strtolower(\str_replace('\\', '_', \get_class($this))))
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
          $result[$row->$id] = $object->loadFromArray((array)$row);
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
  public static function all($columns = array('*'))
  {
    $user = new static;

    return $user->loadAll($columns);
  }
}
