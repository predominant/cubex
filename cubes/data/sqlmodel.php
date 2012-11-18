<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 10:57
 * Description:
 */

namespace Cubex\Data;

abstract class SQLModel extends Model
{
  /*
   * Recommended to override this method in your models
   */
  public function dataConnection()
  {
    return \Cubex\Cubex::db();
  }

  public function loadOneWhere($pattern /* , $arg, $arg, $arg ... */)
  {
    $args = func_get_args();
    array_unshift($args, true);
    $data = call_user_func_array(array($this, 'loadRawWhere'), $args);

    if(count($data) > 1) throw new \Exception("More than one result in loadOneWhere() $pattern");
    $data = reset($data);
    if($data) return $this->loadFromArray($data);
    else return false;
  }

  public function loadAllWhere($pattern /* , $arg, $arg, $arg ... */)
  {
    $args = func_get_args();
    array_unshift($args, true);
    $data = call_user_func_array(array($this, 'loadRawWhere'), $args);

    if($data) return $this->loadMultiFromArray($data);
    else return false;
  }

  public function loadRawWhere($columns, $pattern /* , $arg, $arg, $arg ... */)
  {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    array_unshift($args, $this->getTableName());

    $column = '%LC';
    if(is_bool($columns) || $columns === '*')
    {
      $column = '*';
    }
    else if(is_scalar($columns))
    {
      $columns = explode(',', $columns);
      if(!is_array($columns)) $columns = array($columns);
      array_unshift($args, $columns);
    }
    else
    {
      throw new \Exception("Invalid columns in loadRawWhere()" . print_r($columns, true));
    }

    $pattern = 'SELECT ' . $column . ' FROM %T WHERE ' . $pattern;
    array_unshift($args, $pattern);

    try
    {
      echo \Cubex\Base\Sprintf::parseQuery($this->dataConnection("r"), $args);
    }
    catch(\Exception $e)
    {
      var_dump($e);
    }

    return array();
  }
}
