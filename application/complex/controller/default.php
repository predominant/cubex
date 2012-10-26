<?php
/**
 * User: brooke.bryan
 * Date: 25/10/12
 * Time: 23:16
 * Description:
 */

namespace Cubex\Application\Complex;

class defaultController extends \Cubex\Base\Controller
{

  public function __construct()
  {
    $this->runPage();
  }

  public function runPage()
  {
    echo \Cubex\Core::_(new \Cubex\Base\WebPage())->setTitle("Complex Application")->render();
    Events::pageStarted();
  }
}
