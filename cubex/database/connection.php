<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 01:03
 * Description:
 */

namespace Cubex\Database;

/**
 * Database Connection Rules
 */
interface Connection extends \Cubex\Data\Connection
{

  /**
   * Run a standard query
   *
   * @param $query
   *
   * @return mixed
   */
  public function query($query);

  /**
   * Get a single field
   *
   * @param $query
   *
   * @return mixed
   */
  public function getField($query);

  /**
   * Get a single row
   *
   * @param $query
   *
   * @return mixed
   */
  public function getRow($query);

  /**
   * Get multiple rows
   *
   * @param $query
   *
   * @return mixed
   */
  public function getRows($query);

  /**
   * Get a keyed array based on the first field of the result
   *
   * @param $query
   *
   * @return mixed
   */
  public function getKeyedRows($query);

  /**
   * Number of rows for a query
   *
   * @param $query
   *
   * @return mixed
   */
  public function numRows($query);

  /**
   * Get column names
   *
   * @param $query
   *
   * @return mixed
   */
  public function getColumns($query);
}
