<?php
/**
 * File: EnumTest.php
 * Date: 02/01/13
 * Time: 11:26
 * @author: gareth.evans
 */
namespace Cubex\Tests;

class Type_EnumTest extends \PHPUnit_Framework_TestCase
{
  public function testExceptionThrownWhenBadValuePassed()
  {
    $this->setExpectedException('UnexpectedValueException');

    new Bool('non_value');
  }

  public function testSetAndToString()
  {
    $enum = new Bool(Bool::TRUE);
    $this->assertEquals($enum, Bool::TRUE);
  }

  public function testExcptionThrownWhenNoDefaultSet()
  {
    $this->setExpectedException('UnexpectedValueException');

    new EnumNoDefault();
  }

  public function testExcptionThrownWhenNoConstantsSet()
  {
    $this->setExpectedException('UnexpectedValueException');

    new EnumNoConstants();
  }

  public function testDefaultSetWhenNoValuePassed()
  {
    $enum = new Bool();
    $this->assertEquals($enum, Bool::__default);
  }
}
