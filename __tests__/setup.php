<?php
/**
 * File: setup.php
 * Date: 09/12/12
 * Time: 16:15
 * @author: gareth.evans
 */

namespace Cubex\Tests
{
  require_once dirname(dirname(__FILE__)) .'/cubex/base/cubex.php';
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
      throw new \Exception('Test Exception');
    }

    public function getName()
    {
      return 'ApplicationFailTest';
    }
  }

  class Response extends \Cubex\Http\Response
  {
    public function respond()
    {
      echo $this->_source->renderHead();
      echo $this->_source->renderBody();
      echo $this->_source->renderClosing();
    }
  }

  class Component extends \Cubex\Base\Component
  {

  }

  class Log extends \Cubex\Logger\Log
  {
    public static $log_arguments = array();

    protected static function _log()
    {
      self::$log_arguments = func_get_args();
    }
  }

}

namespace Cubex\Tests\Controllers
{
  class Controller extends \Cubex\Base\Controller
  {
    public function processRequest()
    {
      $webpage = new \Cubex\Base\WebPage();
      $webpage->setTitle("Test Application");

      return new \Cubex\Http\Response($webpage);
    }
  }
}

namespace Cubex\Tests\Views
{

  class ViewTest
  {

  }

}
