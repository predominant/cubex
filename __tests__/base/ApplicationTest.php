<?php
/**
 * File: ApplicationTest.php
 * Date: 22/11/12
 * Time: 17:46
 * @author: gareth.evans <gareth.evans@jdiuk.com>
 */
namespace Cubex\Tests;
class Base_ApplicationTest extends \PHPUnit_Framework_TestCase
{
  public function testExceptionThrownWhenBadParamPassedToInitiator()
  {
    $this->setExpectedException('Exception');

    \Cubex\Application\Application::initialise(new ApplicationFailTest());
  }

  public function testApplicationInitiator()
  {
    // We need to set the request object here as we are mocking a http request
    // even though we're running via the cli
    \Cubex\Cubex::core()->setRequest(new \Cubex\Http\Request());

    $this->expectOutputRegex('/^<!DOCTYPE html>.*/');
    \Cubex\Application\Application::initialise(new Application());
  }

  public function testGetName()
  {
    $this->assertEquals('Application', (new Application())->getName());
  }
}
