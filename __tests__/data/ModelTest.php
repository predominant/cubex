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
  public function testClassImplementsJsonSerializable()
  {
    $expect = json_encode(array('foo' => 'bar', 'bar' => 'foo'));
    $model = new Model();

    $this->assertInstanceOf('\JsonSerializable', $model);
    $this->assertEquals($expect, json_encode($model));
  }
}
