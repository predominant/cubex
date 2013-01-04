<?php
/**
 * File: reflection.php
 * Date: 02/01/13
 * Time: 11:18
 *
 * @author: gareth.evans
 */

namespace Cubex\Type\Enum;

abstract class Reflection
{
  private $_default;
  private $_enum;
  private $_enumsReversed = array();

  private static $_defaultKey = "__default";


  /**
   * @param mixed $enum
   * @param bool  $strict
   */
  public function __construct($enum = null, $strict = false)
  {
    $reflection = new \ReflectionClass($this);
    $constants  = $reflection->getConstants();

    $this->_setDefault($constants)
    ->_setEnums($constants)
    ->_setEnum($enum);
  }

  /**
   * @param array $constants
   *
   * @return Reflection $this
   * @throws \UnexpectedValueException
   */
  private function _setDefault(array $constants)
  {
    if(!isset($constants[self::$_defaultKey]))
    {
      throw new \UnexpectedValueException("No default enum set");
    }

    $this->_default = $constants[self::$_defaultKey];

    return $this;
  }

  /**
   * @param array $constants
   *
   * @return Reflection $this
   * @throws \UnexpectedValueException
   */
  private function _setEnums(array $constants)
  {
    $tempConstants = $constants;

    if(\array_key_exists(self::$_defaultKey, $tempConstants))
    {
      unset($tempConstants[self::$_defaultKey]);
    }

    if(empty($tempConstants))
    {
      throw new \UnexpectedValueException("No constants set");
    }

    $this->_enumsReversed = \array_flip($tempConstants);

    return $this;
  }

  /**
   * @param $enum
   *
   * @return Reflection $this
   * @throws \UnexpectedValueException
   */
  private function _setEnum($enum)
  {
    if($enum === null)
    {
      $enum = $this->_default;
    }

    if(!\array_key_exists($enum, $this->_enumsReversed))
    {
      throw new \UnexpectedValueException("Enum '{$enum}' does not exist");
    }

    $this->_enum = $enum;

    return $this;
  }

  public function __toString()
  {
    return (string)$this->_enum;
  }
}
