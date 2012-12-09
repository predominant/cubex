<?php
/**
 * User: brooke.bryan
 * Date: 09/12/12
 * Time: 17:03
 * Description: Aspects are PHP compiled views as alternatives to phtml files
 */

namespace Cubex\View;

abstract class Aspect implements Renderable
{

  abstract public function render();

  public function __tostring()
  {
    return $this->render();
  }
}
