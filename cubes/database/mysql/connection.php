<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 00:31
 * Description:
 */
namespace Cubex\Database\MySQL;

class Connection implements \Cubex\Database\Connection
{

  /**
   * @var \mysqli
   */
  protected $_connection;
  /**
   * @var \Cubex\Data\Handler
   */
  protected $_config;

  public function __construct(\Cubex\Data\Handler $config)
  {
    $this->_config = $config;

  }

  public function connect($mode = 'w')
  {
    $hostname = $this->_config->getStr('hostname', 'localhost');
    if($mode == 'r')
    {
      $slaves = $this->_config->getArr('slaves', array($hostname));
      shuffle($slaves);
      $hostname = current($slaves);
    }

    $this->_connection = new \mysqli(
      $hostname,
      $this->_config->getStr('username', 'root'),
      $this->_config->getStr('password', ''),
      $this->_config->getStr('database', 'test'),
      $this->_config->getStr('port', 3306)
    );

    return $this;
  }

  public function disconnect()
  {
    $this->_connection->close();
  }

  public function escapeColumnName($column)
  {
    $column = str_replace('`', '', $this->escapeString($column));

    return "`$column`";
  }

  public function escapeString($string)
  {
    return $this->_connection->real_escape_string($string);
  }

  /**
   * @returns \mysqli_result
   */
  protected function doQuery($query)
  {
    return $this->_connection->query($query);
  }

  public function query($query)
  {
    return $this->doQuery($query) === true;
  }

  public function getField($query)
  {
    $result = $this->doQuery($query)->fetch_row();

    return isset($result[0]) ? $result[0] : false;
  }

  public function getRow($query)
  {
    $result = $this->doQuery($query);

    return $result->fetch_object();
  }

  public function getRows($query)
  {
    $result = $this->doQuery($query);
    $rows   = array();
    while($row = $result->fetch_object())
    {
      $rows[] = $row;
    }

    return $rows;
  }

  public function getKeyedRows($query)
  {
    $result         = $this->doQuery($query);
    $rows           = array();
    $keyfield       = $value_key = null;
    $value_as_array = true;
    while($row = $result->fetch_object())
    {
      if($keyfield == null)
      {
        $keyfield = array_keys(get_object_vars($row));
        if(count($keyfield) == 2)
        {
          $value_as_array = false;
          $value_key      = $keyfield[1];
        }
        else if(count($keyfield) == 1)
        {
          $value_as_array = false;
          $value_key      = $keyfield[0];
        }
        $keyfield = $keyfield[0];
      }
      $rows[$row->$keyfield] = !$value_as_array && !empty($value_key) ? $row->$value_key : $row;
    }

    return $rows;
  }

  public function getColumns($query)
  {
    $result = $this->getKeyedRows($query, 0);
    return array_keys($result);
  }

  public function numRows($query)
  {
    $result = $this->doQuery($query);

    return $result->num_rows;
  }

}
