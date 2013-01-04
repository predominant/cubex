<?php
/**
 * User: brooke.bryan
 * Date: 04/01/13
 * Time: 11:37
 * Description:
 */
namespace Cubex\Tests;

use Cubex\ServiceManager\ServiceConfig;

class Locale_FactoryTest extends \PHPUnit_Framework_TestCase
{
  public function testLocaleServiceReturned()
  {
    $factory = new \Cubex\Locale\Factory();
    $this->assertInstanceOf(
      '\Cubex\Locale\Locales', $factory->createService(new ServiceConfig())
    );
  }
}
