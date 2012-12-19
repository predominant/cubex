<?php
/**
 * User: brooke.bryan
 * Date: 15/11/12
 * Time: 19:19
 * Description:
 */
namespace Cubex\Data;

/**
 * Base connection interface
 */
interface Connection
{
  /**
   * Pass through connection configuration
   *
   * @param Handler $configuration
   */
  public function __construct(Handler $configuration);

  /**
   * @param string $mode Either 'r' (reading) or 'w' (reading and writing)
   */
  public function connect($mode = 'w');

  /**
   * Disconnect from the connection
   *
   * @return mixed
   */
  public function disconnect();

  /**
   * Escape column name
   *
   * @param $column
   *
   * @return mixed
   */
  public function escapeColumnName($column);

  /**
   * Escape string value for insert
   *
   * @param $string
   *
   * @return mixed
   */
  public function escapeString($string);
}
