<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 19:41
 * Description:
 */

namespace Cubex\Base;

use Cubex\Cubex;
use Cubex\Language\Translatable;
use Cubex\Routing\Router;
use Cubex\Events\Events;

/**
 * Applications are a base class to group all logic for specific entry points
 */
abstract class Application extends Translatable
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

  /**
   * Application initialiser
   *
   * @param Application $application
   *
   * @throws \Exception
   */
  final public static function initialise(Application $application)
  {
    try
    {
      self::$app = $application;
      self::$app->launch();
    }
    catch(\Exception $e)
    {
      throw new \Exception(
        "Application '" . $application->getName() . "' is unstable\n\n" .
        'From: ' . $e->getFile() . ':' . $e->getLine() . "\n\n" .
        "Message: " . $e->getMessage() . "\n", 503
      );
    }
  }

  /**
   * Launch process for your application
   *
   * Recommended to not override for standard functionality
   *
   */
  public function launch()
  {
    if($this->canLaunch())
    {
      $this->registerAutoLoader();
      $namespace = \substr(\get_called_class(), 0, -12);

      $this->bindLanguage();

      /**
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

      $this->launched();
      Events::trigger(Events::CUBEX_RESPONSE_START);
      Cubex::core()->controller()->getResponse()->respond();
      Events::trigger(Events::CUBEX_RESPONSE_SENT);
    }
    else
    {
      $this->launchFailed();
    }
    $this->shutdown();
  }

  /**
   * Logic for handling application availability
   *
   * @return bool
   */
  public function canLaunch()
  {
    return true;
  }

  /**
   * Called if the launch of your application fails
   */
  public function launchFailed()
  {
  }

  /**
   * Called after the application has been launched
   */
  public function launched()
  {
  }

  /**
   * Name of your application
   *
   * @return string
   */
  public function getName()
  {
    return "";
  }

  /**
   * Description of your application
   *
   * @return string
   */
  public function getDescription()
  {
    return "";
  }

  /**
   * Available components to be used by the application
   *
   * @return array Component Names
   */
  public function getComponents()
  {
    return array();
  }

  /**
   * Default controller classname
   *
   * @return string
   */
  public function getDefaultController()
  {
    return 'DefaultController';
  }

  /**
   * Access routes to be used by the router
   *
   * @return array
   */
  public function getRoutes()
  {
    return array();
  }

  /**
   * Register an application specific auto loader
   *
   * @return null
   */
  public function registerAutoLoader()
  {
    return null;
  }

  /**
   * Get the default application layout
   *
   * @return string
   */
  public function getLayout()
  {
    return $this->_layout;
  }

  /**
   * Set layout file for your application to fall back to if not setup within a controller
   *
   * @param $layout
   *
   * @return Application
   */
  public function setLayout($layout)
  {
    $this->_layout = $layout;

    return $this;
  }

  /**
   * Get data set by the routes
   * Null will return all data, or a specific key can be used to access a specific attribute
   *
   * @param null|string $key
   *
   * @return mixed
   */
  public function getURIData($key = null)
  {
    if($key === null)
    {
      return $this->_uri_data;
    }
    else if(isset($this->_uri_data[$key]))
    {
      return $this->_uri_data[$key];
    }
    else return array();
  }

  /**
   * Return the controller classname for the application to process the request with
   * Accepts the request path for routing
   *
   * @param $path
   *
   * @return null|string
   */
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

  /**
   * Matching route for the request being processed
   *
   * @return string
   */
  public function processedRoute()
  {
    return $this->_processed_route;
  }

  /**
   * Called on application shutdown
   */
  public function shutdown()
  {
  }
}
