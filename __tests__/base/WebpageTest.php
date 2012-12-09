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
    if(!class_exists('Cubex\Applications\Simple\Application'))
    {
      return;
    }
    Cubex\Cubex::core()->setRequest(new Cubex\Http\Request());
    ob_start();
    Cubex\Base\Application::initialise('simple');
    ob_end_clean();
    $this->_webpage = new \Cubex\Base\WebPage();
  }

  private function _getWebpageObject()
  {
    if($this->_webpage === null)
    {
      $this->markTestSkipped(
        "This test requires the cubex_example applications"
      );
    }
    return $this->_webpage;
  }

  public function testGetController()
  {
    $this->assertInstanceOf(
      '\Cubex\Base\Controller',
      $this->_getWebpageObject()->controller()
    );
  }
}
