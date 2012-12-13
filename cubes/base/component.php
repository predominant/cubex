<?php
/**
 * User: brooke.bryan
 * Date: 09/12/12
 * Time: 17:17
 * Description: Components are partial applications
 */

namespace Cubex\Base;

use Cubex\Language\Translatable;

abstract class Component extends Translatable
{
  public function getName()
  {
    return "";
  }

  public function getDescription()
  {
    return "";
  }

  public function getView($view)
  {
    $reflector = new \ReflectionClass(\get_class($this));
    $view = $reflector->getNamespaceName() . '\Views\\' . $view;
    if(\class_exists($view))
    {
      return new $view();
    }
    return false;
  }
}
