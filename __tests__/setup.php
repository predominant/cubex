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
  use Cubex\Cubex;
  use Cubex\Data\SQLModel;
  use Cubex\Type\Enum;

  require_once dirname(dirname(__FILE__)) . '/cubex/cubex.php';
  Cubex::boot();

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
    public static $logArguments = array();

    protected static function _log()
    {
      self::$logArguments = func_get_args();
    }
  }

  class Bool extends Enum
  {
    const __default = self::TRUE;

    const TRUE = "1";
    const FALSE = "0";
  }

  class EnumNoDefault extends Enum
  {
    const TRUE = "1";
    const FALSE = "0";
  }

  class EnumNoConstants extends Enum
  {
    const __default = "0";
  }

  class Model extends SQLModel
  {
    public $foo = 'bar';
    public $bar = 'foo';
  }
}

namespace Cubex\Tests\Controllers
{
  use Cubex\Controller\BaseController;
  use Cubex\Http\Response;
  use Cubex\Response\WebPage;

  class Controller extends BaseController
  {
    public function processRequest()
    {
      $webpage = new WebPage();
      $webpage->setTitle("Test Application");

      return new Response($webpage);
    }
  }
}

namespace Cubex\Tests\Views
{

  class ViewTest
  {

  }
}
