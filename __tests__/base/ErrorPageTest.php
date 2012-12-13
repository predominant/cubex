<?php
/**
 * File: ErrorPageTest.php
 * Date: 13/12/12
 * Time: 19:26
 * @author: gareth.evans
 */
class Base_ErrorPageTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var \Cubex\Base\ErrorPage $_error_page
   */
  private $_error_page;

  protected function setUp()
  {
    $this->_error_page = new \Cubex\Base\ErrorPage(
      404, 'Page Not Found', array('foo' => 'bar')
    );
  }

  public function testGetBodyReturnImplementsRenderableObject()
  {
    $this->assertInstanceOf(
      '\\Cubex\\View\\Renderable', $this->_error_page->getBody()
    );
  }

  public function testParamsGetSet()
  {
    $this->assertStringStartsWith(
      'foo = bar', $this->_error_page->getBody()->render()
    );
  }
}
