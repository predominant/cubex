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
}
