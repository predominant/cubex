<?php
/**
 * File: setup.php
 * Date: 09/12/12
 * Time: 16:15
 * @author: gareth.evans
 */

require_once dirname(dirname(__FILE__)) .'/cubes/base/cubex.php';
\Cubex\Cubex::boot();

final class Setup
{

}

class Application extends \Cubex\Base\Application
{
  public function getDefaultController()
  {
    return 'Controller';
  }

  public function getName()
  {
    return 'Application';
  }
}

class ApplicationFailTest extends \Cubex\Base\Application
{
  public function launch()
  {
    throw new Exception('Test Exception');
  }

  public function getName()
  {
    return 'ApplicationFailTest';
  }
}

class Controller extends \Cubex\Base\Controller
{
  public function processRequest()
  {
    $webpage = new \Cubex\Base\WebPage();
    $webpage->setTitle("Test Application");

    return new Response($webpage);
  }
}

class Response extends \Cubex\Http\Response
{
  public function respond()
  {
    echo $this->_webpage->renderHead();
    echo $this->_webpage->renderBody();
    echo $this->_webpage->renderClosing();
  }
}
