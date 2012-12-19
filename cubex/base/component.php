<?php
/**
 * User: brooke.bryan
 * Date: 09/12/12
 * Time: 17:17
 * Description: Components are partial applications
 */

namespace Cubex\Base;

use Cubex\Language\Translatable;

/**
 * Component
 */
abstract class Component extends Translatable
{
  /**
   * Name of the component
   *
   * @return string
   */
  public function getName()
  {
    return "";
  }

  /**
   * Description for your component
   *
   * @return string
   */
  public function getDescription()
  {
    return "";
  }

  /**
   * View loader by name
   *
   * @param $view
   *
   * @return bool
   */
  public function getView($view)
  {
    $reflector = new \ReflectionClass(\get_class($this));
    $view      = $reflector->getNamespaceName() . '\Views\\' . $view;
    if(\class_exists($view))
    {
      return new $view();
    }
    return false;
  }
}
