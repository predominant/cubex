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

  public function runPage($path = null)
  {
    echo \Cubex\Core::_(new \Cubex\Base\ErrorPage(500, "Control Error", array('path' => $path)))->render();
  }
}
