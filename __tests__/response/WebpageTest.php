<?php
/**
 * File: WebpageTest.php
 * Date: 07/12/12
 * Time: 19:44
 * @author: gareth.evans
 */
namespace Cubex\Tests;
class Response_WebpageTest extends \PHPUnit_Framework_TestCase
{
  /** @var \Cubex\Response\Webpage */
  private $_webpage;

  public function setUp()
  {
    ob_start();
    $request     = new \Cubex\Http\Request();
    $response    = new \Cubex\Http\Response();
    $application = new Application();
    $application->dispatch($request, $response);
    ob_end_clean();
    $this->_webpage = new \Cubex\Response\WebPage();
  }

  public function testNothing()
  {
  }
}
