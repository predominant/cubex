<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 00:31
 * Description:
 */
namespace Cubex\Database\MySQL;

use Cubex\Data\Handler;

/**
 * Connection
 */
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
  private $_connected = false;

  /**
   * @param \Cubex\Data\Handler $config
   */
  public function __construct(Handler $config)
  {
    $this->_config = $config;
  }

  /**
   * @param string $mode
   *
   * @return Connection
   */
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

    $this->_connected = true;

    return $this;
  }

  /**
   *
   */
  public function disconnect()
  {
    $this->_connection->close();
  }

  /**
   * @param $column
   *
   * @return string
   */
  public function escapeColumnName($column)
  {
    $column = str_replace('`', '', $this->escapeString($column));

    return "`$column`";
  }

  /**
   * @param $string
   *
   * @return string
   */
  public function escapeString($string)
  {
    $this->prepareConnection('r');

    return $this->_connection->real_escape_string($string);
  }

  /**
   * @returns \mysqli_result
   */
  protected function doQuery($query)
  {
    return $this->_connection->query($query);
  }

  /**
   * @param string $mode
   */
  protected function prepareConnection($mode = 'r')
  {
    if(!$this->_connected)
    {
      $this->connect($mode);
    }
  }

  /**
   * @param $query
   *
   * @return bool
   */
  public function query($query)
  {
    $this->prepareConnection('w');

    return $this->doQuery($query) === true;
  }

  /**
   * @param $query
   *
   * @return bool
   */
  public function getField($query)
  {
    $this->prepareConnection('r');
    $result = $this->doQuery($query)->fetch_row();

    return isset($result[0]) ? $result[0] : false;
  }

  /**
   * @param $query
   *
   * @return mixed
   */
  public function getRow($query)
  {
    $this->prepareConnection('r');
    $result = $this->doQuery($query);

    return $result->fetch_object();
  }

  /**
   * @param $query
   *
   * @return array
   */
  public function getRows($query)
  {
    $this->prepareConnection('r');
    $result = $this->doQuery($query);
    $rows   = array();
    if($result->num_rows > 0)
    {
      while($row = $result->fetch_object())
      {
        $rows[] = $row;
      }
    }

    try
    {
      if($result)
      {
        $result->close();
      }
    }
    catch(\Exception $e)
    {
      //Oh No
    }

    return $rows;
  }

  /**
   * @param $query
   *
   * @return array
   */
  public function getKeyedRows($query)
  {
    $this->prepareConnection('r');
    $result         = $this->doQuery($query);
    $rows           = array();
    $keyField       = $valueKey = null;
    $valueAsArray = true;
    if($result->num_rows > 0)
    {
      while($row = $result->fetch_object())
      {
        if($keyField == null)
        {
          $keyField = array_keys(get_object_vars($row));
          if(count($keyField) == 2)
          {
            $valueAsArray = false;
            $valueKey      = $keyField[1];
          }
          else if(count($keyField) == 1)
          {
            $valueAsArray = false;
            $valueKey      = $keyField[0];
          }
          $keyField = $keyField[0];
        }
        $rows[$row->$keyField] = !$valueAsArray && !empty($valueKey) ? $row->$valueKey : $row;
      }
    }

    try
    {
      if($result)
      {
        $result->close();
      }
    }
    catch(\Exception $e)
    {
      //Oh No
    }

    return $rows;
  }

  /**
   * @param $query
   *
   * @return array
   */
  public function getColumns($query)
  {
    $this->prepareConnection('r');
    $result = $this->getKeyedRows($query);

    return array_keys($result);
  }

  /**
   * @param $query
   *
   * @return int
   */
  public function numRows($query)
  {
    $this->prepareConnection('r');
    $result = $this->doQuery($query);
    $rows   = (int)$result->num_rows;

    try
    {
      if($result)
      {
        $result->close();
      }
    }
    catch(\Exception $e)
    {
      //Oh No
    }

    return $rows;
  }
}
