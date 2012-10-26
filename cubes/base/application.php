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

  public function launch($launch_default_controller = true)
  {
    $this->registerAutoLoader();
    $namespace = substr(get_called_class(), 0, -12);

    /*
     * Initiate Event Hooks
     */
    $events = $namespace . "\\Events";
    if(class_exists($events))
    {
      $events::createHooks();
    }

    /*
     * Initiate Controller
     */
    if($launch_default_controller)
    {
      $c = $namespace . "\\" . $this->getDefaultController();
      if(class_exists($c))
      {
        new $c();
      }
      else
      {
        \Cubex\Cubex::fatal("No controller could be located for " . $this->getName());
      }
    }
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
