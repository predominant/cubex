<?php
/**
 * User: brooke.bryan
 * Date: 04/12/12
 * Time: 17:00
 * Description:
 */

namespace Cubex\Routing;

class Router
{

  const ROUTE_DYNAMIC = 'route:dynamic';
  protected $_processedRoute = '';
  protected $_routeData = array();

  public function parseRoute($routes, $path, $prepend = '')
  {
    if(!\is_array($routes)) return null;
    foreach($routes as $route => $control)
    {
      //Parse route
      $attempt = $this->tryRoute($prepend . $route . (empty($route) ? '$' : ''), $path);

      //Import any matched URI Data
      if($attempt[0] && \is_array($attempt[1]))
      {
        foreach($attempt[1] as $k => $v)
        {
          $this->_routeData[$k] = $v;
        }
      }

      if(\is_array($control))
      {
        $this->_processedRoute = $prepend . $route;

        return $this->parseRoute($control, $path, $prepend . $route);
      }
      else if($attempt[0])
      {
        $this->_processedRoute = $prepend . $route;
        $control               = $this->dynamicRoute($control);
        return $control;
      }
    }

    return null;
  }

  /**
   * Validate for dynamic route
   *
   * @param $control
   *
   * @return mixed
   */
  protected function dynamicRoute($control)
  {
    if($control == self::ROUTE_DYNAMIC && isset($this->_routeData["_dynamic"]))
    {
      $control = ltrim($this->_routeData["_dynamic"], '/');
      $control = str_replace(' ', '', ucwords(str_replace('/', ' ', $control)));
    }
    return $control;
  }

  protected function tryRoute($route, $path, $second = false)
  {
    if(\substr($path, -1) != '/') $path = $path . '/';
    $data  = $matches = array();
    $match = \preg_match("#^$route#", $path, $matches);
    foreach($matches as $k => $v)
    {
      //Strip out all non declared matches
      if(!\is_numeric($k)) $data[$k] = $v;
    }

    /* Allow Simple Routes */
    if(!$second && !$match && \stristr($route, ':'))
    {
      $retry = \preg_replace("/\:(_?[a-zA-Z]+)/", "(?P<$1>[^\/]+)", $route);

      return $this->tryRoute($retry, $path, true);
    }

    return array($match, $data);
  }

  public function processedRoute()
  {
    return $this->_processedRoute;
  }

  public function getRouteData($key = null)
  {
    if($key === null)
    {
      return $this->_routeData;
    }
    else if(isset($this->_routeData[$key]))
    {
      return $this->_routeData[$key];
    }
    else return array();
  }
}
