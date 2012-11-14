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
  private $_layout = 'default';

  final public static function initialise($application)
  {
    $class_name = "\\Cubex\\Application\\$application\\Application";
    if(class_exists($class_name))
    {
      \Cubex\Core::_(new $class_name)->launch();
    }
    else throw new \Exception("Application '" . $application . "' is unavailable",503);
  }

  public function launch($launch_default_controller = true)
  {
    $this->registerAutoLoader();
    $namespace = substr(get_called_class(), 0, -12);

    /*
     * Initiate Event Hooks
     */
    $events = $namespace . "\\Events";
    if(class_exists($events) && $events instanceof \Cubex\Events\Events)
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

  public function getLayout()
  {
    return $this->_layout;
  }

  public function setLayout($layout)
  {
    $this->_layout = $layout;
  }


}
