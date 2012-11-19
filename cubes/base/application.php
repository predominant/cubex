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

  private $_uri_data = array();
  private $_layout = 'default';

  final public static function initialise($application)
  {
    $class_name = "\\Cubex\\Application\\$application\\Application";
    if(class_exists($class_name))
    {
      \id(new $class_name)->launch();
    }
    else throw new \Exception("Application '" . $application . "' is unavailable", 503);
  }

  public function launch()
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
    $c = $namespace . "\\" . $this->getController(\Cubex\Cubex::request()->getPath());
    ;
    if(class_exists($c))
    {
      new $c();
    }
    else
    {
      \Cubex\Cubex::fatal("No controller could be located for " . $this->getName());
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
    return 'defaultController';
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

  public function getURIData($key = null)
  {
    if($key === null) return $this->_uri_data;
    else if(isset($this->_uri_data[$key])) return $this->_uri_data[$key];
    else return array();
  }

  private function getController($path)
  {
    return $this->parseRoute($this->getRoutes(), $path);
  }

  private function parseRoute($routes, $path, $prepend = '')
  {
    if(!is_array($routes)) return $this->getDefaultController();
    foreach($routes as $route => $control)
    {
      //Parse route
      $attempt = $this->tryRoute($prepend . $route . (empty($route) ? '$' : ''), $path);

      //Import any matched URI Data
      if($attempt[0] && is_array($attempt[1]))
      {
        foreach($attempt[1] as $k => $v)
        {
          $this->_uri_data[$k] = $v;
        }
      }

      if(is_array($control)) return $this->parseRoute($control, $path, $prepend . $route);
      else if($attempt[0]) return $control;
    }

    return $this->getDefaultController();
  }

  private function tryRoute($route, $path)
  {
    if(substr($path, -1) != '/') $path = $path . '/';
    $data  = $matches = array();
    $match = preg_match("#^$route#", $path, $matches);
    foreach($matches as $k => $v)
    {
      //Strip out all non declared matches
      if(!is_numeric($k)) $data[$k] = $v;
    }

    return array($match, $data);
  }

}
