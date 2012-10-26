<?php
/**
 * User: brooke.bryan
 * Date: 25/10/12
 * Time: 23:16
 * Description:
 */

namespace Cubex\Application\Simple;

class defaultController extends \Cubex\Base\Controller
{
  public function runPage()
  {
    echo \Cubex\Core::_(new \Cubex\Base\WebPage())->setTitle("Simple Application")->render();
  }
}
