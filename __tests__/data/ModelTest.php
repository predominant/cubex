<?php
/**
 * File: ModelTest.php
 * Date: 06/01/13
 * Time: 10:14
 * @author: gareth.evans
 */

namespace Cubex\Tests;

class Data_ModelTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var Model $_model
   */
  private $_model;
  private $_modelAttributes;

  public function setUp()
  {
    $this->_model = new Model();
    $this->_modelAttributes = array('foo' => 'bar', 'bar' => 'foo');
  }

  public function testClassImplementsJsonSerializable()
  {
    $this->assertInstanceOf('\JsonSerializable', $this->_model);
    $this->assertEquals(
      json_encode($this->_modelAttributes),
      json_encode($this->_model)
    );
  }

  public function testIterator()
  {
    $iterator = $this->_model->getIterator();

    $this->assertInstanceOf('\ArrayIterator', $iterator);
    $this->assertEquals($this->_modelAttributes, $iterator->getArrayCopy());
  }

  public function testClone()
  {
    $modelClone = clone $this->_model;

    $this->assertEquals($modelClone, $this->_model);
  }

  public function testToString()
  {
    $modelToStringExpected = "Cubex\\Tests\\Model {foo = bar, bar = foo}";

    $this->assertEquals($modelToStringExpected, (string)$this->_model);
  }

  public function testGetAndSet()
  {
    $this->assertEquals('foo', $this->_model->getBar());
    $this->assertEquals('foo', $this->_model->bar);

    $this->_model->setFoo('bartest');
    $this->_model->bar = 'footest';

    $this->assertEquals('bartest', $this->_model->getFoo());
    $this->assertEquals('footest', $this->_model->bar);

    // Make sure we change everything back.
    $this->_model->foo = 'bar';
    $this->_model->bar = 'foo';

    $this->setExpectedException('Exception', 'Invalid Attribute testset');
    $this->_model->setTestSet(null);
    $this->setExpectedException('Exception', 'Invalid Attribute testget');
    $this->_model->getTestGet();
  }

  public function testGetTableName()
  {
    $this->assertEquals('cubex_tests_model', $this->_model->getTableName());
  }
}
