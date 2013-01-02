<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 19:41
 * Description:
 */

namespace Cubex\Project;

use Cubex\Base\Dispatchable;
use Cubex\Controller\BaseController;
use Cubex\Cubex;
use Cubex\Http\Request;
use Cubex\Http\Response;
use Cubex\Language\Translatable;
use Cubex\Routing\Router;
use Cubex\Event\Events;

/**
 * Applications are a base class to group all logic for specific entry points
 */
abstract class Application extends Translatable implements Dispatchable
{
  /**
   * @var Request
   */
  protected $_request;
  /**
   * @var Response
   */
  protected $_response;
  private $_uriData = array();
  protected $_processedRoute = '';
  private $_layout = 'default';
  protected static $app = null;

  /**
   * @return Application
   */
  final public static function getApp()
  {
    return self::$app;
  }

  public function dispatch(Request $request, Response $response)
  {
    static::$app     = $this;
    $this->_request  = $request;
    $this->_response = $response;

    Cubex::core()->appendClassMap(Cubex::loadClassMap($this->filePath()));
    Events::trigger(Events::CUBEX_APPLICATION_PRELAUNCH, [], $this);
    $this->launch();
    Events::trigger(Events::CUBEX_APPLICATION_POSTLAUNCH, [], $this);

    $this->shutdown();
    return $this->_response;
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
      $this->bindLanguage();

      /**
       * Initiate Controller
       */
      $controller = $this->getController($this->_request->getPath());
      if($controller !== null && $controller instanceof BaseController)
      {
        Cubex::core()->setController($controller);
        $controller->setApp($this)->dispatch($this->_request, $this->_response);
      }
      else
      {
        throw new \Exception("No controller could be located for " . $this->getName());
      }
      $this->launched();
    }
    else
    {
      $this->launchFailed();
    }
  }

  /**
   * Logic for handling application availability
   *
   * @return bool
   */
  public function canLaunch()
  {
    Events::trigger(Events::CUBEX_APPLICATION_CANLAUNCH, [], $this);
    return true;
  }

  /**
   * Called if the launch of your application fails
   */
  public function launchFailed()
  {
    Events::trigger(Events::CUBEX_APPLICATION_LAUNCHFAIL, [], $this);
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
   * Default controller
   *
   * @return \Cubex\Controller\BaseController
   */
  abstract public function getDefaultController();

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
      return $this->_uriData;
    }
    else if(isset($this->_uriData[$key]))
    {
      return $this->_uriData[$key];
    }
    else return array();
  }

  /**
   * Return the controller classname for the application to process the request with
   * Accepts the request path for routing
   *
   * @param $path
   *
   * @throws \BadMethodCallException
   *
   * @return BaseController
   */
  protected function getController($path)
  {
    $router     = new Router();
    $controller = $router->parseRoute($this->getRoutes(), $path);
    if(\is_array($this->_uriData))
    {
      $this->_uriData = \array_merge($this->_uriData, $router->getRouteData());
    }
    else $this->_uriData = $router->getRouteData();

    $this->_processedRoute = $router->processedRoute();

    if($controller === null)
    {
      return $this->getDefaultController();
    }
    else if($controller instanceof BaseController)
    {
      return $controller;
    }
    else
    {
      if(class_exists($controller))
      {
        $controller = new $controller();
        if($controller instanceof BaseController)
        {
          return $controller;
        }
      }
      throw new \BadMethodCallException("Invalid Controller $controller");
    }
  }

  /**
   * Matching route for the request being processed
   *
   * @return string
   */
  public function processedRoute()
  {
    return $this->_processedRoute;
  }

  /**
   * Called on application shutdown
   */
  public function shutdown()
  {
    Events::trigger(Events::CUBEX_APPLICATION_SHUTDOWN, [], $this);
  }
}
