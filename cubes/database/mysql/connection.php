<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 00:31
 * Description:
 */
namespace Cubex\Database\MySQL;

use Cubex\Data\Handler;

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

  public function __construct(Handler $config)
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

    $this->_connected = true;

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

  protected function prepareConnection($mode = 'r')
  {
    if(!$this->_connected)
    {
      $this->connect($mode);
    }
  }

  public function query($query)
  {
    $this->prepareConnection('w');

    return $this->doQuery($query) === true;
  }

  public function getField($query)
  {
    $this->prepareConnection('r');
    $result = $this->doQuery($query)->fetch_row();

    return isset($result[0]) ? $result[0] : false;
  }

  public function getRow($query)
  {
    $this->prepareConnection('r');
    $result = $this->doQuery($query);
    return $result->fetch_object();
  }

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

  public function getKeyedRows($query)
  {
    $this->prepareConnection('r');
    $result         = $this->doQuery($query);
    $rows           = array();
    $keyfield       = $value_key = null;
    $value_as_array = true;
    if($result->num_rows > 0)
    {
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

  public function getColumns($query)
  {
    $this->prepareConnection('r');
    $result = $this->getKeyedRows($query);

    return array_keys($result);
  }

  public function numRows($query)
  {
    $this->prepareConnection('r');
    $result = $this->doQuery($query);
    $rows = (int)$result->num_rows;

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
