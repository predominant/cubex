<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 10:57
 * Description:
 */

namespace Cubex\Model;

use Cubex\Cubex;
use Cubex\Logger\Debug;

/**
 * Database Model
 */
abstract class SQLModel extends DataModel
{

  /**
   * Recommended to override this method in your models
   */
  protected function dataConnection()
  {
    return Cubex::core()->getServiceManager()->db();
  }

  /**
   * @param $pattern
   *
   * @return bool|Model
   * @throws \Exception
   */
  public function loadOneWhere($pattern /* , $arg, $arg, $arg ... */)
  {
    $args = \func_get_args();
    \array_unshift($args, true);
    $data = \call_user_func_array(array($this, 'loadRawWhere'), $args);

    if(\count($data) > 1)
    {
      throw new \Exception("More than one result in loadOneWhere() $pattern");
    }
    $data = \reset($data);
    if($data)
    {
      return $this->hydrate($data);
    }
    else return false;
  }

  /**
   * @param $pattern
   *
   * @return array|bool
   */
  public function loadAllWhere($pattern /* , $arg, $arg, $arg ... */)
  {
    $args = \func_get_args();
    \array_unshift($args, true);
    $data = \call_user_func_array(array($this, 'loadRawWhere'), $args);

    if($data)
    {
      return $this->multiHydrate($data);
    }
    else return false;
  }

  /**
   * @param array $columns
   *
   * @return array|bool
   */
  public function loadAll($columns = array("*"))
  {
    $data = $this->loadRawWhere($columns, "1=1");
    if($data)
    {
      return $this->multiHydrate($data);
    }
    else return false;
  }

  /**
   * @param \Cubex\Data\SearchObject $o
   *
   * @return array|bool
   */
  public function loadMatches(\Cubex\Data\SearchObject $o)
  {
    return self::loadAllWhere("%QO", $o);
  }

  /**
   * @param $columns
   * @param $pattern
   *
   * @return bool|mixed
   * @throws \Exception
   */
  public function loadRawWhere($columns, $pattern /* , $arg, $arg, $arg ... */)
  {
    $args = \func_get_args();
    \array_shift($args);
    \array_shift($args);
    \array_unshift($args, $this->getTableName());

    $column = '%LC';
    if(\is_bool($columns) || $columns === '*' || (\is_array($columns) && $columns[0] == '*'))
    {
      $column = '*';
    }
    else if(\is_array($columns))
    {
      \array_unshift($args, $columns);
    }
    else if(\is_scalar($columns))
    {
      $columns = \explode(',', $columns);
      if(!\is_array($columns))
      {
        $columns = array($columns);
      }
      \array_unshift($args, $columns);
    }
    else
    {
      throw new \Exception("Invalid columns in loadRawWhere()" . \print_r($columns, true));
    }

    $pattern = 'SELECT ' . $column . ' FROM %T WHERE ' . $pattern;
    \array_unshift($args, $pattern);

    $query = \Cubex\Data\Sprintf::parseQuery($this->dataConnection("r"), $args);

    if($query !== false)
    {
      return $this->dataConnection()->getRows($query);
    }
    else return false;
  }

  /**
   * @param       $id
   * @param array $columns
   *
   * @return bool
   */
  public function load($id, $columns = array("*"))
  {
    $this->setExists(false);
    if(\is_array($id))
    {
      $id = $this->composeID($id);
    }

    $data = $this->loadRawWhere($columns, $this->idPattern(), $this->getIDKey(), $id);
    if(is_array($data) && !empty($data))
    {
      $this->{$this->getIDKey()} = $id;
      $this->hydrate((array)\current($data));
      $this->setExists(true);
      return true;
    }
    else return false;
  }

  /**
   * @return string
   */
  protected function idPattern()
  {
    $config = $this->getConfiguration();
    if($config[self::CONFIG_IDS] == self::ID_AUTOINCREMENT)
    {
      return "%C = %d";
    }
    else
    {
      return "%C = %s";
    }
  }

  /**
   * @return mixed
   */
  public function saveChanges()
  {
    $modified = $this->getModifiedAttributes();
    $updates  = $inserts = array();
    foreach($modified as $attr)
    {
      if($attr instanceof \Cubex\Data\Attribute)
      {
        if($attr->isModified())
        {
          $inserts[$attr->getName()] = $attr->serialize();
          $updates[]                 = \Cubex\Data\Sprintf::parseQuery(
            $this->dataConnection("w"), array("%C = %ns", $attr->getName(), $attr->serialize())
          );
          $attr->unsetModified();
        }
      }
    }

    if(empty($updates))
    {
      return true;
    }

    if(!$this->exists())
    {
      $pattern = "INSERT INTO %T (%LC) VALUES(%Ls)";

      $args = array(
        $this->getTableName(),
        array_keys($inserts),
        array_values($inserts),
      );

      if($this->getID() !== null)
      {
        $pattern .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updates);
        $pattern .= ' WHERE ' . $this->idPattern();
        $args[] = $this->getIDKey();
        $args[] = $this->getID();
      }

      array_unshift($args, $pattern);

      $query = \Cubex\Data\Sprintf::parseQuery(
        $this->dataConnection("w"),
        $args
      );
    }
    else
    {
      $pattern = 'UPDATE %T SET ' . implode(', ', $updates) . ' WHERE ' . $this->idPattern();
      $args    = array($pattern, $this->getTableName(), $this->getIDKey(), $this->getID());
      $query   = \Cubex\Data\Sprintf::parseQuery($this->dataConnection("w"), $args);
    }

    Debug::info($query, 0, 'Query');

    return $this->dataConnection()->query($query);
  }
}
