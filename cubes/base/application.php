<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 19:41
 * Description:
 */

namespace Cubex\Base;

class Application
{
  final public static function initialise($application)
  {
    $class_name = "\\Cubex\\Application\\$application\\Application";
    \Cubex\Core::_(new $class_name)->launch();
  }

  public function launch()
  {
    echo "Launching " . $this->getName() . "\n\n<br/>";
    echo $this->getDescription();
  }

  public function getName()
  {
    return "";
  }

  public function getDescription()
  {
    return "";
  }

  public function requiredApplications()
  {
    return array();
  }

  public function getBaseURI()
  {
    return "/";
  }

  public function getDefaultController()
  {
    return 'Controller';
  }

  public function getRoutes()
  {
    return array();
  }

  public function registerAutoLoader()
  {
    return null;
  }
}
