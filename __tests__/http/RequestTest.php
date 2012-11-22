<?php
/**
 * File: RequestTest.php
 * Date: 22/11/12
 * Time: 18:57
 * @author: gareth.evans <gareth.evans@jdiuk.com>
 */
class Http_RequestTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Cubex\Http\Request
   */
  protected $_empty_request;
  /**
   * @var Cubex\Http\Request
   */
  protected $_built_request;

  const REQUEST_HOST = 'www.example.com';
  const REQUEST_PATH = '/foo/bar';

  public function setUp()
  {
    $this->_empty_request = new Cubex\Http\Request();
    $this->_built_request = new Cubex\Http\Request(
      static::REQUEST_PATH, static::REQUEST_HOST
    );
  }

  public function testSetAndGetPathAndHostMethods()
  {
    $this->_empty_request->setPath(static::REQUEST_PATH);
    $this->assertEquals(static::REQUEST_PATH, $this->_empty_request->getPath());
    $this->_empty_request->setHost(static::REQUEST_HOST);
    $this->assertEquals(static::REQUEST_HOST, $this->_empty_request->getHost());
  }

  public function testGetPathAndHostMethods()
  {
    $this->assertEquals(static::REQUEST_PATH, $this->_built_request->getPath());
    $this->assertEquals(static::REQUEST_HOST, $this->_built_request->getHost());
  }

  public function testOtherGetMethods()
  {
    $this->assertEquals('com', $this->_built_request->getTld());
    $this->assertEquals('www', $this->_built_request->getSubDomain());
    $this->assertEquals('example', $this->_built_request->getDomain());
  }
}
