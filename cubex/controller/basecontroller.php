<?php
/**
 * User: brooke.bryan
 * Date: 16/10/12
 * Time: 13:55
 * Description:
 */

namespace Cubex\Controller;

use Cubex\Base\Dispatchable;
use \Cubex\Cubex;
use Cubex\Http\Request;
use \Cubex\Project\Application;
use \Cubex\Data\Handler;
use \Cubex\Response\ErrorPage;
use \Cubex\Http\Response;
use \Cubex\Routing\Router;
use \Exception;
use \Cubex\Application\Layout;

/**
 * Base Controller
 */
abstract class BaseController implements Dispatchable, \IteratorAggregate
{
  use \Cubex\Traits\Data\Handler;

  /**
   * @var Request
   */
  protected $_request;
  /**
   * @var Response
   */
  protected $_response;

  /**
   * @var Application
   */
  private $_app;

  /**
   * @var \Exception
   */
  protected $_exception;

  protected $_delegated = false;

  public static $_layout;

  public function setApp(Application $app)
  {
    $this->_app = $app;
    return $this;
  }

  public function getApp()
  {
    return $this->_app;
  }

  public function dispatch(Request $request, Response $response)
  {
    $this->_request  = $request;
    $this->_response = $response;

    /* Populate data set with routes */
    $uri_data = $this->getApp()->getURIData();
    if($uri_data !== null)
    {
      foreach($uri_data as $k => $v)
      {
        $this->setData($k, $v);
      }
    }

    $this->setData('_route', $this->getApp()->processedRoute());
    $this->setData('_path', $this->request()->getPath());

    try
    {
      $canProcess = $this->canProcess();
      if(!$canProcess)
      {
        throw new \Exception("Unable to process request");
      }

      $this->preProcess();
      $this->_response->fromSource($this->processRequest());
      $this->postProcess();
    }
    catch(\Exception $e)
    {
      $this->_exception = $e;
      $this->_response->fromSource($this->failedProcess($e));
    }

    return $this->_response;
  }

  /* Handling the request */

  /**
   * @return \Cubex\Http\Request
   */
  public function request()
  {
    return Cubex::request();
  }

  /**
   * @return \Cubex\Http\Response
   */
  public function getResponse()
  {
    return $this->_response;
  }

  /**
   * @param $response
   *
   * @return BaseController
   */
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
   *
   * @return Response
   */
  public function failedProcess(Exception $e)
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

    return $webpage;
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

  /**
   * @return array
   */
  public function getPostRoutes()
  {
    return array();
  }

  /**
   * @return array
   */
  public function getRoutes()
  {
    return array();
  }

  /**
   * @return array
   */
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
   * ajax requests will attempt: ajaxIndex()
   * post requests will attempt: postIndex()
   * final attempt will be to: renderIndex()
   *
   * @returns Response
   * @throws \BadMethodCallException
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

  /**
   * @return string
   */
  protected function controllerName()
  {
    $reflector = new \ReflectionClass(\get_class($this));

    return $reflector->getShortName();
  }

  /**
   * @return null|string
   */
  protected function defaultAction()
  {
    return null;
  }

  /**
   * Delegate control to a new controller
   *
   * @param BaseController $newController
   *
   * @return bool
   */
  public function delegate(BaseController $newController)
  {
    \ob_get_clean();
    $this->_delegated = true;

    return $newController->getResponse();
  }

  /* View Related Bits */

  /**
   * @return string
   */
  public function getLayout()
  {
    return $this->getApp()->getLayout();
  }

  /**
   * @param $layout
   *
   * @return BaseController
   */
  public function setLayout($layout)
  {
    $this->getApp()->setLayout($layout);

    return $this;
  }

  /**
   * Create new layout object
   *
   * @return \Cubex\Application\Layout
   */
  protected function createLayout()
  {
    return new Layout($this->getApp());
  }

  /**
   * @return \Cubex\Application\Layout
   */
  public function currentLayout()
  {
    if(self::$_layout === null)
    {
      self::$_layout = $this->createLayout();
    }
    return self::$_layout;
  }

  /**
   * Specify a CSS file to include (/css | .css are not required)
   *
   * @param $file
   *
   * @return BaseController
   */
  public function requireCss($file)
  {
    $this->getApp()->requireCss($file);
    return $this;
  }

  /**
   * Specify a JS file to include (/js | .js are not required)
   *
   * @param $file
   *
   * @return BaseController
   */
  public function requireJs($file)
  {
    $this->getApp()->requireJs($file);
    return $this;
  }

  /**
   * Require all $type files with the request
   *
   * @param string $type
   *
   * @return BaseController
   */
  public function requirePackage($type = 'css')
  {
    $this->getApp()->requirePackage($type);
    return $this;
  }


  /**
   * Translate string to locale
   *
   * @param $message string $string
   *
   * @return string
   */
  public function t($message)
  {
    return $this->getApp()->t($message);
  }

  /**
   * Translate plural
   *
   * @param      $singular
   * @param null $plural
   * @param int  $number
   *
   * @return string
   */
  public function p($singular, $plural = null, $number = 0)
  {
    return $this->getApp()->p($singular, $plural, $number);
  }

  /**
   * Translate plural, converting (s) to '' or 's'

   */
  public function tp($text, $number)
  {
    $this->getApp()->tp($text, $number);
  }
}
