<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 01:03
 * Description:
 */

namespace Cubex\Database;

interface Connection extends \Cubex\Data\Connection
{

  public function query($query);

  public function getField($query);

  public function getRow($query);

  public function getRows($query);

  public function getKeyedRows($query);

  public function numRows($query);

  public function getColumns($query);
}
