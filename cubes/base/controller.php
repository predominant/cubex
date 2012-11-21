<?php
/**
 * User: brooke.bryan
 * Date: 16/10/12
 * Time: 13:55
 * Description:
 */

namespace Cubex\Base;

class Controller
{

  public function __construct()
  {
    $this->runPage(\Cubex\Cubex::request()->getPath());
  }

  /**
   * @return Application
   */
  public function app()
  {
    return \Cubex\Base\Application::getApp();
  }

  public function processedRoute()
  {
    return $this->App()->processedRoute();
  }

  public function getLayout()
  {
    return $this->app()->getLayout();
  }

  public function setLayout($layout)
  {
    $this->app()->setLayout($layout);
    return $this;
  }

  public function runPage($path = null)
  {
    $this->renderInvalidAction($path);
  }

  public function renderInvalidAction($path=null)
  {
    $webpage = new \Cubex\Base\ErrorPage(500, "Control Error : Invalid Action", array('path' => $path));
    new \Cubex\Http\Response($webpage);
  }
}
