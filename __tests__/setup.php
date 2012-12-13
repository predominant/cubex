<?php
/**
 * File: setup.php
 * Date: 09/12/12
 * Time: 16:15
 * @author: gareth.evans
 */

require_once dirname(dirname(__FILE__)) .'/cubes/boot.php';

final class Setup {

}

final class Application extends \Cubex\Base\Application
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

final class ApplicationFailTest extends \Cubex\Base\Application
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

final class Controller extends \Cubex\Base\Controller
{
  public function processRequest()
  {
    $webpage = new \Cubex\Base\WebPage();
    $webpage->setTitle("Test Application");

    return new Response($webpage);
  }
}

final class Response extends \Cubex\Http\Response
{
  public function respond()
  {
    echo $this->_webpage->renderHead();
    echo $this->_webpage->renderBody();
    echo $this->_webpage->renderClosing();
  }
}
