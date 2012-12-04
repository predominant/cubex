<?php
/**
 * User: brooke.bryan
 * Date: 16/10/12
 * Time: 13:55
 * Description:
 */

namespace Cubex\Base;

abstract class Controller extends \Cubex\Data\Handler
{

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

    if($this->canProcess())
    {
      $this->preProcess();
      $this->processRequest();
      $this->postProcess();
    }
    else
    {
      $this->failedProcess();
    }
  }

  /*
   * @return Application
   */
  public function app()
  {
    return \Cubex\Base\Application::getApp();
  }

  /* Handling the request */

  /*
   * @return Http\Request
   */
  public function request()
  {
    return \Cubex\Cubex::request();
  }

  /*
   * Should the continue process, or run failedProcess()
   */
  public function canProcess()
  {
    return true;
  }

  /*
   * response when canProcess() returns false
   */
  public function failedProcess()
  {
    $webpage = new \Cubex\Base\ErrorPage(500, "Failed to Process", array('path' => $this->request()->getPath()));
    new \Cubex\Http\Response($webpage);
  }

  /*
   * Any pre filtering
   */
  public function preProcess()
  {
  }

  /*
   * Main method for handling the request
   */
  public function processRequest()
  {
    if(!$this->routeRequest())
    {
      $webpage = new \Cubex\Base\ErrorPage(500, "Unhandled Request", array('path' => $this->request()->getPath()));
      new \Cubex\Http\Response($webpage);
    }
  }

  /*
   * Any post processing
   */
  public function postProcess()
  {
  }

  /* Routing */

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
      'post' => $this->postRoutes(),
      'ajax' => $this->ajaxRoutes(),
      'base' => $this->routes()
    );
  }

  /*
   * Look for ajax specific routes, then post specific routes, then all other routes
   * */
  public function routeRequest()
  {
    $path = $this->request()->getPath();
    $router = new \Cubex\Routing\Router();
    if($this->request()->isAjax())
    {
      $action = $router->parseRoute($this->getAjaxRoutes(), $path);
      if($action !== null) return $this->processRouteReturn($action);
    }

    if($this->request()->isHTTPPost())
    {
      $action = $router->parseRoute($this->getPostRoutes(), $path);
      if($action !== null) return $this->processRouteReturn($action);
    }

    $action = $router->parseRoute($this->getRoutes(), $path);
    if($action !== null) return $this->processRouteReturn($action);
    return false;
  }

  /*
   * example action = index
   *
   * ajax requests will attempt: ajaxIndex()
   * post requests will attempt: postIndex()
   * final attempt will be to: renderIndex()
   *
   * */
  protected function processRouteReturn($action)
  {
    if($action === null) return false;

    if($this->request()->isAjax())
    {
      $attempt = 'ajax' . ucfirst($action);
      if(method_exists($this,$attempt))
      {
        $this->$attempt();
        return true;
      }
    }

    if($this->request()->isHTTPPost())
    {
      $attempt = 'post' . ucfirst($action);
      if(method_exists($this,$attempt))
      {
        $this->$attempt();
        return true;
      }
    }

    $attempt = 'render' . ucfirst($action);
    if(method_exists($this,$attempt))
    {
      $this->$attempt();
      return true;
    }

    return false;
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

  public function baseView()
  {
    return new \Cubex\View\View('layout' . DIRECTORY_SEPARATOR . $this->getLayout(), $this->app());
  }

  /**
   * Translate string to locale, wrapper for gettext
   *
   * @link http://php.net/manual/en/function.gettext.php
   * @param $message string $string
   * @return string
   */
  public function t($message)
  {
    return $this->app()->t($message);
  }

  /**
   * Translate plural, using specific domain
   *
   * @link http://php.net/manual/en/function.dngettext.php
   * @param      $singular
   * @param null $plural
   * @param int  $number
   * @return string
   */
  public function p($singular, $plural = null, $number = 0)
  {
    return $this->app()->p($singular, $plural, $number);
  }
}
