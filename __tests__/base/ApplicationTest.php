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
      'Exception', 'Application \'ApplicationFailTest\' is unavailable'
    );

    Cubex\Base\Application::initialise(new ApplicationFailTest());
  }

  public function testApplicationInitiator()
  {
    // We need to set the request object here as we are mocking a http request
    // even though we're running via the cli
    Cubex\Cubex::core()->setRequest(new Cubex\Http\Request());

    // The response object that gets used to ouput a webpage flushes the page
    // in stages for some good reasons, but it means that PHPUnit gets a bit
    // confused so we'll just grab the whole output and echo it outselves
    $this->expectOutputRegex('/^<!DOCTYPE html>.*/');
    Cubex\Base\Application::initialise(new Application());
  }
}
