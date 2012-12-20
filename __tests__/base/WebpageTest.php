<?php
/**
 * File: WebpageTest.php
 * Date: 07/12/12
 * Time: 19:44
 * @author: gareth.evans
 */
namespace Cubex\Tests;
class Base_WebpageTest extends \PHPUnit_Framework_TestCase
{
  /** @var \Cubex\Base\Webpage */
  private $_webpage;

  public function setUp()
  {
    \Cubex\Cubex::core()->setRequest(new \Cubex\Http\Request());
    ob_start();
    $application = new Application();
    \Cubex\Application\Application::initialise($application);
    ob_end_clean();
    $this->_webpage = new \Cubex\Base\WebPage();
  }

  public function testGetController()
  {
    $this->assertInstanceOf(
      '\Cubex\Controller\BaseController',
      $this->_webpage->controller()
    );
  }
}
