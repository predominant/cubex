<?php
/**
 * File: ApplicationTest.php
 * Date: 22/11/12
 * Time: 17:46
 * @author: gareth.evans <gareth.evans@jdiuk.com>
 */
class Base_ApplicationTest extends PHPUnit_Framework_TestCase
{
  public function testExceptionThrownWhenBadParamPassedToInitiator()
  {
    $this->setExpectedException(
      'Exception', 'Application \'unittest\' is unavailable'
    );

    Cubex\Base\Application::initialise('unittest');
  }

  public function testSimpleApplicationInitiator()
  {
    if(!class_exists('Cubex\Applications\Simple\Application'))
    {
      $this->markTestSkipped(
        "This test requires the cubex_example applications"
      );
    }

    // We need to set the request object here as we are mocking a http request
    // even though we're running via the cli
    Cubex\Cubex::core()->setRequest(new Cubex\Http\Request());
    $this->expectOutputRegex('/^<!DOCTYPE html>.*/');
    Cubex\Base\Application::initialise('simple');
  }
}
