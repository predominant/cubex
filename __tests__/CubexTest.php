<?php
/**
 * File: CubexTest.php
 * Date: 27/10/12
 * Time: 13:45
 * @author: gareth.evans <gareth.evans@jdiuk.com>
 */
class CubexTest extends PHPUnit_Framework_TestCase
{
  public function testCubexHasLoaded()
  {
    $this->assertTrue(class_exists('\\Cubex\\Cubex'), "Cubex has not been loaded");
  }

  public function testCubexHasInstantiated()
  {
    $this->assertInstanceOf('\\Cubex\\Cubex', \Cubex\Cubex::$cubex);
  }

  public function testCubexConfigurationExists()
  {
    $configuration = \Cubex\Cubex::configuration();
    $this->assertInstanceOf('\\Cubex\\Data\\Handler', $configuration);
    $this->assertEquals('Development Platform', $configuration->environment);
  }
}
