<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 01:03
 * Description:
 */

namespace Cubex\Session;

/**
 * Session container
 */
interface Container
{

  /**
   * @param array $configuration
   */
  public function __construct(array $configuration);

  /**
   * @param $key
   * @param $data
   *
   * @return mixed
   */
  public function set($key, $data);

  /**
   * @param $key
   *
   * @return mixed
   */
  public function get($key);

  public function destroy();
}
