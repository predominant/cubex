<?php
/**
 * User: brooke.bryan
 * Date: 29/11/12
 * Time: 21:10
 * Description:
 */

namespace Cubex\View;

/**
 * An object that can be rendered with __tostring() or with render()
 */
interface Renderable
{

  public function render();

  public function __tostring();
}
