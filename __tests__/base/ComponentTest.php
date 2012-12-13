<?php
/**
 * File: ComponentTest.php
 * Date: 13/12/12
 * Time: 19:48
 * @author: gareth.evans
 */
namespace Cubex\Tests;
use Cubex\Tests\Views\ViewTest;

class Base_ComponentTest extends \PHPUnit_Framework_TestCase
{
  private $_component;

  protected function setUp()
  {
    $this->_component = new Component();
  }

  public function testGetName()
  {
    $this->assertTrue($this->_component->getName() === '');
  }

  public function testGetDescription()
  {
    $this->assertTrue($this->_component->getDescription() === '');
  }

  public function testGetView()
  {
    $this->assertEquals(
      new ViewTest(),
      $this->_component->getView('ViewTest')
    );

    $this->assertFalse($this->_component->getView('iDontExist'));
  }
}
