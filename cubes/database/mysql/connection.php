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

  public function __construct(array $config = array())
  {
  }

  public function connect($mode='w')
  {

  }

  public function disconnect()
  {

  }

  public function escapeColumnName($column)
  {
    return "`$column`";
  }

  public function escapeString($string)
  {
    return $string;
  }

  public function escapeStringForLikeClause($string)
  {
    return $string;
  }

  public function escapeMultilineComment($comment)
  {
    return $comment;
  }
}
