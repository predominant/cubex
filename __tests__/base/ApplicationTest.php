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
}
