<?php
/**
 * User: brooke.bryan
 * Date: 07/01/13
 * Time: 18:12
 * Description:
 */

namespace Cubex\Model;

interface Storage
{
  /**
   * Process for storing a model, e.g. a database or file
   *
   * @param Model $object
   *
   * @return bool
   */
  public static function store(Model $object);
}
