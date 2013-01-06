<?php
/**
 * File: LayoutTest.php
 * Date: 06/01/13
 * Time: 10:55
 * @author: gareth.evans
 */
namespace Cubex\Tests;

use Cubex\Tests\Application\Layout;
use Cubex\View\Render;

class Application_LayoutTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var Layout $_layout
   */
  private $_layout;
  private $_applicationOne;

  public function setUp()
  {
    $this->_applicationOne = new Application();
    $this->_layout = new Layout($this->_applicationOne);
  }

  public function testAppGetAndSet()
  {
    $applicationTwo = new Application2();

    $this->assertEquals($this->_applicationOne, $this->_layout->getApp());

    $this->_layout->setApp($applicationTwo);

    $this->assertEquals($applicationTwo, $this->_layout->getApp());
  }

  public function testLayoutTemplateSet()
  {
    $this->assertEquals('default', $this->_layout->getLayoutTemplate());

    $this->_layout->setLayoutTemplate('testTemplate');

    $this->assertEquals('testTemplate', $this->_layout->getLayoutTemplate());

    // Change it back
    $this->_layout->setLayoutTemplate('default');
  }

  public function testNesting()
  {
    $render = new Render('nestTest');
    $renderBefore = new Render('nestTestBefore');
    $renderAfter = new Render('nestTestAfter');
    $layout = clone $this->_layout;

    $this->assertFalse($layout->isNested('nestTest'));
    $layout->nest('nestTest', $render);
    $this->assertTrue($layout->isNested('nestTest'));

    // Just make sure it doesn't return true for everything once the nest()
    // method has been called
    $this->assertFalse($layout->isNested('nestTest2'));

    // Test Nest Before and after, they use the same functions but we're going
    // to run the tests on both just to be safe
    $this->assertArrayNotHasKey(
      'nestTestBefore', $layout->getRenderHooks()['before']
    );
    $layout->nestBefore('nestTestBefore', $renderBefore);
    $this->assertArrayHasKey(
      'nestTestBefore', $layout->getRenderHooks()['before']
    );
    $this->assertEquals(
      $renderBefore, $layout->getRenderHooks()['before']['nestTestBefore'][0]
    );

    $this->assertArrayNotHasKey(
      'nestTestAfter', $layout->getRenderHooks()['after']
    );
    $layout->nestAfter('nestTestAfter', $renderAfter);
    $this->assertArrayHasKey(
      'nestTestAfter', $layout->getRenderHooks()['after']
    );
    $this->assertEquals(
      $renderAfter, $layout->getRenderHooks()['after']['nestTestAfter'][0]
    );

    return $layout;
  }

  /**
   * @depends testNesting
   */
  public function testRender(Layout $nestingLayout)
  {
    $renderOutput = $this->_layout->render();
    $this->assertEquals("default.phtml.test\n", $renderOutput);

    $renderBefore = $nestingLayout->renderNest('nestTestBefore');
    $this->assertEquals(
      '<div id="nestTestBefore">nestTestBefore</div>', $renderBefore
    );

    $renderAfter = $nestingLayout->renderNest('nestTestAfter');
    $this->assertEquals(
      '<div id="nestTestAfter">nestTestAfter</div>', $renderAfter
    );

    $render = $nestingLayout->renderNest('nestTest');
    $this->assertEquals(
      '<div id="nestTest">nestTest</div>', $render
    );

    $render = $nestingLayout->renderNest('nestTest', 'nestTestNewDivId');
    $this->assertEquals(
      '<div id="nestTestNewDivId">nestTest</div>', $render
    );
  }
}
