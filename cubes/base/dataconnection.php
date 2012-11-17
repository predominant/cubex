<?php
/**
 * User: brooke.bryan
 * Date: 15/11/12
 * Time: 19:19
 * Description:
 */
namespace Cubex\Base;

interface DataConnection
{
  public function __construct(array $config = array());

  /*
   * @param $mode string Mode, either 'r' (reading) or 'w' (reading and writing)
   */
  public function connect($mode = 'w');

  public function disconnect();

  public function escapeColumnName($column);

  public function escapeString($string);

  public function escapeStringForLikeClause($string);

  public function escapeMultilineComment($comment);
}
