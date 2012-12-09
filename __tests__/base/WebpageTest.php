<?php
/**
 * File: WebpageTest.php
 * Date: 07/12/12
 * Time: 19:44
 * @author: gareth.evans
 */
class Base_WebpageTest extends PHPUnit_Framework_TestCase
{
  /** @var \Cubex\Base\Webpage */
  private $_webpage;

  public function setUp()
  {
    Cubex\Cubex::core()->setRequest(new Cubex\Http\Request());
    ob_start();
    $application = new Application();
    Cubex\Base\Application::initialise($application);
    ob_end_clean();
    $this->_webpage = new \Cubex\Base\WebPage();
  }

  public function testGetController()
  {
    $this->assertInstanceOf(
      '\Cubex\Base\Controller',
      $this->_webpage->controller()
    );
  }
}
