<?php
/**
 * User: Brooke Bryan
 * Date: 14/10/12
 * Time: 01:31
 * Description: Helper Methods
 */

namespace Cubex;

class Core
{

  /**
   * Unfortunate oversight by PHP's part meaning that you can't chain an
   * object from it's instantiation;
   *
   * new Foo()->Bar(); //throws an error
   *
   * C:_(new Foo())->Bar(); //works lovely
   *
   * @static
   * @param $object
   * @return mixed
   */
  final public static function _($object)
  {
    return $object;
  }
}
