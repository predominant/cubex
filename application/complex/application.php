<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 19:40
 * Description:
 */

namespace Cubex\Application\Complex;

class Application extends \Cubex\Base\Application
{
  public function getName()
  {
    return "Complex Application";
  }

  public function getDescription()
  {
    return "Complex application to demonstrate full functionality of the Cubex framework";
  }

  public function requiredApplications()
  {
    return array('simple');
  }

  public function getBaseURI()
  {
    return "/complex/";
  }

  public function getDefaultController()
  {
    return 'defaultController';
  }

  public function getRoutes()
  {
    return array();
  }

  public static function loadClass($classname)
  {
    $base = dirname(__FILE__);
  }

  public function registerAutoLoader()
  {
    spl_autoload_register("self::loadClass",true,true);
  }
}
