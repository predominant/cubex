<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 10:57
 * Description:
 */

namespace Cubex\Data;

use Cubex\Cubex;

abstract class SQLModel extends Model
{

  /**
   * Recommended to override this method in your models
   */
  public function dataConnection()
  {
    return Cubex::db();
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
      throw new \Exception("More than one result in loadOneWhere() $pattern");
    $data = \reset($data);
    if($data)
      return $this->loadFromArray($data);
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
      return $this->loadMultiFromArray($data);
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
      return $this->loadMultiFromArray($data);
    else return false;
  }

  /**
   * @param SearchObject $o
   *
   * @return array|bool
   */
  public function loadMatches(SearchObject $o)
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
        $columns = array($columns);
      \array_unshift($args, $columns);
    }
    else
    {
      throw new \Exception("Invalid columns in loadRawWhere()" . \print_r($columns, true));
    }

    $pattern = 'SELECT ' . $column . ' FROM %T WHERE ' . $pattern;
    \array_unshift($args, $pattern);

    $query = Sprintf::parseQuery($this->dataConnection("r"), $args);

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
    if(\is_array($id))
    {
      $id = $this->composeID($id);
    }

    $data = $this->loadRawWhere($columns, $this->idPattern(), $this->getIDKey(), $id);
    if(is_array($data))
    {
      $this->loadFromStdClass(\current($data));

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
    $updates  = array();
    foreach($modified as $attr)
    {
      if($attr instanceof Attribute)
      {
        if($attr->isModified())
        {
          /*echo "\n<br/>";
          echo "Setting: " . $attr->getName() . "\n<br/>";
          echo "Changing from " . $attr->originalData() . " to " . $attr->rawData() . "\n<br/>";*/
          $updates[] = Sprintf::parseQuery(
            $this->dataConnection("w"), array("%C = %ns", $attr->getName(), $attr->serialize())
          );
          $attr->unsetModified();
        }
      }
    }

    $pattern = 'UPDATE %T SET ' . implode(', ', $updates) . ' WHERE ' . $this->idPattern();
    $args    = array($pattern, $this->getTableName(), $this->getIDKey(), $this->getID());
    $query   = Sprintf::parseQuery($this->dataConnection("w"), $args);

    return $this->dataConnection()->query($query);
  }
}
