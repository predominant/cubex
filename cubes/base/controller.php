<?php
/**
 * User: brooke.bryan
 * Date: 16/10/12
 * Time: 13:55
 * Description:
 */

namespace Cubex\Base;

use \Cubex\Cubex;
use \Cubex\Base\Application;
use \Cubex\Data\Handler;
use \Cubex\Base\ErrorPage;
use \Cubex\Http\Response;
use \Cubex\Routing\Router;
use \Cubex\View\Template;

abstract class Controller extends Handler
{

  /** @var Response */
  protected $_response;
  /** @var \Exception */
  protected $_exception;

  protected $_delegated = false;

  final public function __construct()
  {
    /* Populate data set with routes */
    $uri_data = $this->app()->getURIData();
    if($uri_data !== null)
    {
      foreach($uri_data as $k => $v)
      {
        $this->setData($k, $v);
      }
    }

    $this->setData('_route', $this->app()->processedRoute());
    $this->setData('_path', $this->request()->getPath());

    try
    {
      $can_process = $this->canProcess();
      if(!$can_process)
      {
        throw new \Exception("Unable to process request");
      }

      $this->preProcess();
      $this->setResponse($this->processRequest());
      $this->postProcess();
    }
    catch(\Exception $e)
    {
      $this->_exception = $e;
      $this->setResponse($this->failedProcess($e));
    }
  }

  /**
   * @return Application
   */
  public function app()
  {
    return Application::getApp();
  }

  /* Handling the request */

  /**
   * @return \Cubex\Http\Request
   */
  public function request()
  {
    return Cubex::request();
  }

  public function getResponse()
  {
    return $this->_response;
  }

  public function setResponse($response)
  {
    if($response instanceof Response)
    {
      $this->_response = $response;
    }
    else
    {
      $this->_response = new Response($response);
    }

    return $this;
  }

  /**
   * Should the continue process, or run failedProcess()
   *
   * @return bool
   * @throws \Exception
   */
  public function canProcess()
  {
    return true;
  }


  /**
   * /**
   * response when canProcess() returns false
   *
   * @param \Exception $e
   * @return Response
   */
  public function failedProcess(\Exception $e)
  {
    $webpage = new ErrorPage(
      500, $e->getMessage(),
      array(
           'path'             => $this->request()->getPath(),
           'exception'        => $e->getMessage(),
           'exception_code'   => $e->getCode(),
           'exception_source' => $e->getFile() . ':' . $e->getLine(),
      )
    );

    return new Response($webpage);
  }

  /**
   * Any pre filtering
   */
  public function preProcess()
  {
  }

  /**
   * Main method for handling the request
   *
   * @return Response
   */
  public function processRequest()
  {
    $response = $this->routeRequest();

    return $response;
  }

  /**
   * Any post processing
   */
  public function postProcess()
  {
  }

  /** Routing */

  public function getAjaxRoutes()
  {
    return array();
  }

  public function getPostRoutes()
  {
    return array();
  }

  public function getRoutes()
  {
    return array();
  }

  final public function getAllRoutes()
  {
    return array(
      'post' => $this->getPostRoutes(),
      'ajax' => $this->getAjaxRoutes(),
      'base' => $this->getRoutes()
    );
  }

  /**
   * Look for ajax specific routes, then post specific routes, then all other routes
   *
   * @returns Response
   * @throws \Exception
   **/
  public function routeRequest()
  {
    $path   = $this->request()->getPath();
    $router = new Router();
    $action = null;

    if($action === null && $this->request()->isAjax())
    {
      $action = $router->parseRoute($this->getAjaxRoutes(), $path);
    }

    if($action === null && $this->request()->isHTTPPost())
    {
      $action = $router->parseRoute($this->getPostRoutes(), $path);
    }

    if($action === null)
    {
      $action = $router->parseRoute($this->getRoutes(), $path);
    }

    if($action === null)
    {
      $action = $this->defaultAction();
    }

    if($action !== null)
    {
      foreach($router->getRouteData() as $k => $v)
      {
        $this->setData($k, $v);
      }

      return $this->processRouteReturn($action);
    }

    throw new \Exception("Unable to route request");
  }

  /**
   * example action = index
   *
   * ajax requests will attempt: ajaxIndex()
   * post requests will attempt: postIndex()
   * final attempt will be to: renderIndex()
   *
   * @returns Response
   * @throws \BadMethodCallException
   *
   * */
  protected function processRouteReturn($action)
  {
    if($action === null)
    {
      throw new \BadMethodCallException("No action specified on " . $this->controllerName());
    }

    if($this->request()->isAjax())
    {
      $attempt = 'ajax' . \ucfirst($action);
      if(\method_exists($this, $attempt))
      {
        return $this->$attempt();
      }
    }

    if($this->request()->isHTTPPost())
    {
      $attempt = 'post' . \ucfirst($action);
      if(\method_exists($this, $attempt))
      {
        return $this->$attempt();
      }
    }

    $attempt = 'render' . \ucfirst($action);
    if(\method_exists($this, $attempt))
    {
      return $this->$attempt();
    }

    throw new \BadMethodCallException("Invalid action $action specified on " . $this->controllerName());
  }

  protected function controllerName()
  {
    $reflector = new \ReflectionClass(\get_class($this));

    return $reflector->getShortName();
  }

  protected function defaultAction()
  {
    return null;
  }

  /**
   * Delegate control to a new controller
   *
   * @param Controller $new_controller
   * @return bool
   */
  public function delegate(Controller $new_controller)
  {
    \ob_get_clean();
    $this->_delegated = true;
    Cubex::core()->setController($new_controller);

    return $new_controller->getResponse();
  }

  /* View Related Bits */

  public function getLayout()
  {
    return $this->app()->getLayout();
  }

  public function setLayout($layout)
  {
    $this->app()->setLayout($layout);

    return $this;
  }

  public function baseTemplate()
  {
    return new Template('layout' . DIRECTORY_SEPARATOR . $this->getLayout(), $this->app());
  }


  /**
   * Translate string to locale
   *
   * @param $message string $string
   * @return string
   */
  public function t($message)
  {
    return $this->app()->t($message);
  }

  /**
   * Translate plural
   *
   * @param      $singular
   * @param null $plural
   * @param int  $number
   * @return string
   */
  public function p($singular, $plural = null, $number = 0)
  {
    return $this->app()->p($singular, $plural, $number);
  }

  /**
   *
   * Translate plural, converting (s) to '' or 's'
   *
   */
  public function tp($text, $number)
  {
    $this->app()->tp($text, $number);
  }
}
