<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 19:41
 * Description:
 */

namespace Cubex\Base;

class Application extends Translatable
{
  private $_uri_data = array();
  protected $_processed_route = '';
  private $_layout = 'default';
  /* @var $app Application */
  public static $app = null;

  /**
   * @return Application
   */
  final public static function getApp()
  {
    return self::$app;
  }

  final public static function initialise($application)
  {
    $class_name = "\\Cubex\\Application\\$application\\Application";
    if(class_exists($class_name))
    {
      self::$app = new $class_name;
      self::$app->launch();
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

    $this->bindLanguage();

    /*
     * Initiate Controller
     */
    $controller = $namespace . "\\" . $this->getController(\Cubex\Cubex::request()->getPath());
    if(class_exists($controller))
    {
      \Cubex\Cubex::core()->setController(new $controller());
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

    return $this;
  }

  public function getURIData($key = null)
  {
    if($key === null) return $this->_uri_data;
    else if(isset($this->_uri_data[$key])) return $this->_uri_data[$key];
    else return array();
  }

  protected function getController($path)
  {
    return $this->parseRoute($this->getRoutes(), $path);
  }

  protected function parseRoute($routes, $path, $prepend = '')
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

      if(is_array($control))
      {
        $this->_processed_route = $prepend . $route;

        return $this->parseRoute($control, $path, $prepend . $route);
      }
      else if($attempt[0])
      {
        $this->_processed_route = $prepend . $route;

        return $control;
      }
    }

    return $this->getDefaultController();
  }

  protected function tryRoute($route, $path, $second = false)
  {
    if(substr($path, -1) != '/') $path = $path . '/';
    $data  = $matches = array();
    $match = preg_match("#^$route#", $path, $matches);
    foreach($matches as $k => $v)
    {
      //Strip out all non declared matches
      if(!is_numeric($k)) $data[$k] = $v;
    }

    /* Allow Simple Routes */
    if(!$second && !$match && stristr($route, ':'))
    {
      $retry = preg_replace("/\:([a-zA-Z]+)/", "(?P<$1>[^\/]+)", $route);

      return $this->tryRoute($retry, $path, true);
    }

    return array($match, $data);
  }

  public function processedRoute()
  {
    return $this->_processed_route;
  }

}
