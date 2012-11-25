<?php
/**
 * User: brooke.bryan
 * Date: 21/10/12
 * Time: 14:42
 * Description:
 */

namespace Cubex\Base;

class Module extends Translatable
{
  final private function getModule()
  {
    return $this;
  }

  public function moduleVersion()
  {
    return '1.0';
  }

  public function moduleName()
  {
    return str_replace('Cubex\\Module\\','',get_class(static::getModule()));
  }

  public function moduleDescription()
  {
    return "";
  }
}
