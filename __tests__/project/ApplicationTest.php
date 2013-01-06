<?php
/**
 * File: ApplicationTest.php
 * Date: 22/11/12
 * Time: 17:46
 *
 * @author: gareth.evans <gareth.evans@jdiuk.com>
 */
namespace Cubex\Tests;

class Project_ApplicationTest extends \PHPUnit_Framework_TestCase
{
  public function testExceptionThrownWhenBadParamPassedToInitiator()
  {
    $this->setExpectedException('Exception');

    $request  = new \Cubex\Http\Request();
    $response = new \Cubex\Http\Response();
    $application = new ApplicationFailTest();
    $application->dispatch($request, $response);
  }

  public function testApplicationRender()
  {
    // We need to set the request object here as we are mocking a http request
    // even though we're running via the cli
    $request  = new \Cubex\Http\Request();
    $response = new \Cubex\Http\Response();

    $application = new Application();
    $respond     = $application->dispatch($request, $response);

    echo $respond->respond();

    $this->expectOutputRegex('/^<!DOCTYPE html>.*/');
  }

  public function testGetName()
  {
    $this->assertEquals('Application', (new Application())->getName());
  }
}
