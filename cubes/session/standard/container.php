<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 00:31
 * Description:
 */
namespace Cubex\Session\Standard;

class Container implements \Cubex\Session\Container
{

  public function __construct(array $config)
  {
    \session_start();
    if(!isset($_SESSION['cubex'])) $_SESSION['cubex'] = array();
  }

  public function get($key)
  {
    return $_SESSION['cubex'][$key];
  }

  public function set($key, $data)
  {
    $_SESSION['cubex'][$key] = $data;
  }

  public function destroy()
  {
    return \session_destroy();
  }
}
