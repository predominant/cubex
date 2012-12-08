<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 19:41
 * Description:
 */

namespace Cubex\Base;

use \Cubex\Cubex;
use \Cubex\Language\Translatable;
use \Cubex\Routing\Router;

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

  final public static function initialise(Application $application)
  {
    try
    {
      self::$app = $application;
      self::$app->launch();
    }
    catch(\Exception $e)
    {
      throw new \Exception("Application '" . $application->getName() . "' is unavailable", 503);
    }
  }

  public function launch()
  {
    $this->registerAutoLoader();
    $namespace = \substr(\get_called_class(), 0, -12);

    /*
     * Initiate Event Hooks
     */
    $events = $namespace . "\\Events";
    if(\class_exists($events) && \is_subclass_of($events, '\\Cubex\\Events\\Events'))
    {
      $events::createHooks();
    }

    $this->bindLanguage();

    /*
     * Initiate Controller
     */
    $controller = $namespace . "\\" . $this->getController(Cubex::request()->getPath());
    if(\class_exists($controller))
    {
      Cubex::core()->setController(new $controller());
    }
    else
    {
      Cubex::fatal("No controller could be located for " . $this->getName());
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

  public function getBaseURI()
  {
    return "/";
  }

  public function getComponents()
  {
    return array();
  }

  public function getDefaultController()
  {
    return 'DefaultController';
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
    $router     = new Router();
    $controller = $router->parseRoute($this->getRoutes(), $path);
    if(\is_array($this->_uri_data))
    {
      $this->_uri_data = \array_merge($this->_uri_data, $router->getRouteData());
    }
    else $this->_uri_data = $router->getRouteData();

    $this->_processed_route = $router->processedRoute();

    return $controller === null ? $this->getDefaultController() : $controller;
  }

  public function processedRoute()
  {
    return $this->_processed_route;
  }

}
