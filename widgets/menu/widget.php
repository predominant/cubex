<?php
/**
 * User: brooke.bryan
 * Date: 13/11/12
 * Time: 09:10
 * Description:
 */

namespace Cubex\Widgets\Menu;

class Widget extends \Cubex\View\Widget
{

  private $_items;

  public function render()
  {
    $this->preRender();
    $content = "<h3>" . Constants::TITLE . "</h3>";
    $content .= $this->getCapturedContent();

    if($this->_items !== null)
    {
      $content .= '<ul>';
      foreach($this->_items as $item)
      {
        $content .= '<li><a href="'. $item['link'] .'">'. $item['text'] .'</a></li>';
      }
      $content .= '</ul>';
    }

    return $content;
  }

  public function addItem($text, $link = '#')
  {
    $this->_items[] = array('text' => $text, 'link' => $link);
  }
}
