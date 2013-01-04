<?php
/**
 * File: RedirectTest.php
 * Date: 09/12/12
 * Time: 17:27
 * @author: gareth.evans
 */
namespace Cubex\Tests;
class Http_RedirectTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var \Cubex\Http\Redirect
   */
  private $_redirect;

  const SETUP_URL = 'http://www.google.com/';
  const SETUP_HTTP_STATUS = 302;
  const URL = 'http://www.google.co.uk/';
  const HTTP_STATUS = 200;

  protected function setUp()
  {
    ob_start();

    parent::setUp();

    $this->_redirect = new \Cubex\Http\Redirect(
      self::SETUP_URL, self::SETUP_HTTP_STATUS
    );
  }

  protected function tearDown()
  {
    header_remove();
    parent::tearDown();
  }

  public function testSetAndGetUrl()
  {
    $this->_redirect->setUrl(self::URL);
    $this->assertEquals(self::URL, $this->_redirect->getUrl());
  }

  public function testSetAndGetHttpStatus()
  {
    $this->_redirect->setHttpStatus(self::HTTP_STATUS);
    $this->assertEquals(self::HTTP_STATUS, $this->_redirect->getHttpStatus());
  }

  public function testSetAndGetDieRender()
  {
    $this->_redirect->setDieRender(true);
    $this->assertTrue($this->_redirect->getDieRender());
    $this->_redirect->setDieRender(false);
  }

  public function testRedirect()
  {
    $this->_redirect->redirect();
    if(!function_exists('xdebug_get_headers'))
    {
      $this->markTestSkipped("Test needs xdebug installed to get headers");
    }
    $headers_list = xdebug_get_headers();
    $this->assertNotEmpty($headers_list);
    $excpected_url = $this->_redirect->getUrl();
    $this->assertContains("Location: {$excpected_url}", $headers_list);
  }
}
