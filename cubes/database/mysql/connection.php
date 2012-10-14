<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 00:31
 * Description:
 */
namespace Database\MySQL;

class Connection implements \Database\Connection
{

  public function __construct(array $config)
  {
    echo '<pre>';
    print_r($config);
    echo '</pre>';
    echo "MySQL Loaded";
  }

  public function GetRow($sql)
  {}
}
