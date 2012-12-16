<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 00:31
 * Description:
 */
namespace Cubex\Session\Standard;

/**
 * Standard PHP Session handler
 */
class Container implements \Cubex\Session\Container
{

  /**
   * @param array $config
   */
  public function __construct(array $config)
  {
    \session_start();
    if(!isset($_SESSION['cubex'])) $_SESSION['cubex'] = array();
  }

  /**
   * @param $key
   *
   * @return mixed
   */
  public function get($key)
  {
    return $_SESSION['cubex'][$key];
  }

  /**
   * @param $key
   * @param $data
   *
   * @return mixed|void
   */
  public function set($key, $data)
  {
    $_SESSION['cubex'][$key] = $data;
  }

  /**
   * @return bool
   */
  public function destroy()
  {
    return \session_destroy();
  }
}
