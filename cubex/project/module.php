<?php
/**
 * User: brooke.bryan
 * Date: 21/10/12
 * Time: 14:42
 * Description:
 */

namespace Cubex\Project;

use \Cubex\Language\Translatable;

/**
 * Base module
 */
abstract class Module extends Translatable
{

  /**
   * @return Module
   */
  final private function getModule()
  {
    return $this;
  }

  /**
   * @return string
   */
  public function moduleVersion()
  {
    return '1.0';
  }

  /**
   * @return string
   */
  public function moduleName()
  {
    return \str_replace('Cubex\\Module\\', '', \get_class(static::getModule()));
  }

  /**
   * @return string
   */
  public function moduleDescription()
  {
    return "";
  }
}
