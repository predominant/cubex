<?php
/**
 * User: brooke.bryan
 * Date: 01/12/12
 * Time: 13:33
 * Description: Render Groups
 */

namespace Cubex\View;

class Group implements Renderable
{
  protected $_items = array();

  public function add(Renderable $item/*, $item, $item */)
  {
    $items = func_get_args();
    foreach($items as $itm)
    {
      if($itm instanceof Renderable)
      {
        $this->_items[] = $itm;
      }
    }

    return $this;
  }

  public function render()
  {
    $render = '';
    foreach($this->_items as $item)
    {
      if($item instanceof Renderable)
      {
        $render .= $item->render();
      }
    }
    return $render;
  }
}
