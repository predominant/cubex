<?php
/**
 * File: ErrorPageTest.php
 * Date: 13/12/12
 * Time: 19:26
 * @author: gareth.evans
 */
namespace Cubex\Tests;
class Response_ErrorPageTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var \Cubex\Response\ErrorPage $_error_page
   */
  private $_errorPage;

  protected function setUp()
  {
    $this->_errorPage = new \Cubex\Response\ErrorPage(
      404, 'Page Not Found', array('foo' => 'bar')
    );
  }

  public function testGetBodyReturnImplementsRenderableObject()
  {
    $this->assertInstanceOf(
      '\\Cubex\\View\\Renderable', $this->_errorPage->getBody()
    );
  }

  public function testParamsGetSet()
  {
    $this->assertStringStartsWith(
      'foo = bar', $this->_errorPage->getBody()->render()
    );
  }
}
