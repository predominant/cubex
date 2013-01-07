<?php
/**
 * User: brooke.bryan
 * Date: 07/01/13
 * Time: 18:19
 * Description:
 */

namespace Cubex\Model;

class Collection implements \Countable, \JsonSerializable, \IteratorAggregate
{
  protected $_models;

  /**
   * @param array $models
   */
  public function __construct(array $models = [])
  {
    $this->models = $models;
  }


  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Retrieve an external iterator
   *
   * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
   * @return \Traversable An instance of an object implementing <b>Iterator</b> or
   * <b>Traversable</b>
   */
  public function getIterator()
  {
    return new \ArrayIterator($this->_models);
  }

  /**
   * (PHP 5 &gt;= 5.1.0)<br/>
   * Count elements of an object
   *
   * @link http://php.net/manual/en/countable.count.php
   * @return int The custom count as an integer.
   * </p>
   * <p>
   *       The return value is cast to an integer.
   */
  public function count()
  {
    return count($this->_models);
  }

  /**
   * (PHP 5 >= 5.4.0)
   * Serializes the object to a value that can be serialized natively by json_encode().
   *
   * @link http://docs.php.net/manual/en/jsonserializable.jsonserialize.php
   * @return mixed Returns data which can be serialized by json_encode(),
   *       which is a value of any type other than a resource.
   */
  public function jsonSerialize()
  {
    return $this->_models;
  }
}
