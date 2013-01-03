<?php
/**
 * File: CubexTest.php
 * Date: 27/10/12
 * Time: 13:45
 * @author: gareth.evans <gareth.evans@jdiuk.com>
 */
namespace Cubex\Tests;
class Base_CubexTest extends \PHPUnit_Framework_TestCase
{
  public function testCubexHasLoaded()
  {
    $this->assertTrue(
      class_exists('Cubex\Cubex'),
      "Cubex has not been loaded"
    );
  }

  public function testCubexHasInstantiated()
  {
    $cubex = \Cubex\Cubex::$cubex;
    $this->assertInstanceOf('Cubex\Cubex', $cubex);

    return $cubex;
  }

  /**
   * @depends testCubexHasInstantiated
   * @param $cubex \Cubex\Cubex
   */
  public function testCubexConfigurationExists($cubex)
  {
    $configuration = $cubex::configuration();
    $this->assertInstanceOf('Cubex\Config\Config', $configuration);
    $this->assertEquals(
      'Development Platform',
      $configuration->getStr('environment')
    );
  }
}
