<?php
/**
 * File: setup.php
 * Date: 09/12/12
 * Time: 16:15
 *
 * @author: gareth.evans
 */

namespace Cubex\Tests
{
  require_once dirname(dirname(__FILE__)) . '/cubex/cubex.php';
  \Cubex\Cubex::boot();

  final class Setup
  {

  }

  class Application extends \Cubex\Project\Application
  {
    /**
     * Default controller
     *
     * @return \Cubex\Controller\BaseController
     */
    public function getDefaultController()
    {
      return new Controllers\Controller();
    }

    public function getName()
    {
      return 'Application';
    }
  }

  class ApplicationFailTest extends \Cubex\Project\Application
  {
    public function launch()
    {
      throw new \Exception('Test Exception');
    }

    public function getName()
    {
      return 'ApplicationFailTest';
    }

    /**
     * Default controller
     *
     * @return \Cubex\Controller\BaseController
     */
    public function getDefaultController()
    {
      return new Controllers\Controller();
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

  class Component extends \Cubex\Project\Component
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

  class Bool extends \Cubex\Type\Enum
  {
    const __default = self::TRUE;

    const TRUE = "1";
    const FALSE = "0";
  }

  class EnumNoDefault extends \Cubex\Type\Enum
  {
    const TRUE = "1";
    const FALSE = "0";
  }

  class EnumNoConstants extends \Cubex\Type\Enum
  {
    const __default = "0";
  }
}

namespace Cubex\Tests\Controllers
{
  class Controller extends \Cubex\Controller\BaseController
  {
    public function processRequest()
    {
      $webpage = new \Cubex\Response\WebPage();
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
