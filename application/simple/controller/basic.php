<?php
/**
 * User: brooke.bryan
 * Date: 25/10/12
 * Time: 23:16
 * Description:
 */

namespace Cubex\Application\Simple;

class basicController extends \Cubex\Base\Controller
{

  public function runPage($path)
  {
    echo $this->processedRoute();
    echo $path;
    echo \id(new \Cubex\Base\WebPage())->setTitle("Simple Application : Basic")->render();
    echo "Basic";
  }
}
