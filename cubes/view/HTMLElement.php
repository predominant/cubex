<?php
/**
 * User: brooke.bryan
 * Date: 04/12/12
 * Time: 15:58
 * Description:
 */

namespace Cubex\View;

class HTMLElement implements Renderable
{

  private $_tag = '';
  private $_nested = array();
  private $_attributes = array();
  private $_content = '';

  public static function create($tag = '', $content = '', $attributes = array())
  {
    return new HTMLElement($tag, $content, $attributes);
  }

  public function __construct($tag = '', $content = '', $attributes = array())
  {
    $this->_tag        = $tag;
    $this->_content    = $content;
    $this->_attributes = $attributes;
  }

  public function setContent($content)
  {
    $this->_content = $content;

    return $this;
  }

  public function nestTag($tag, $content, $attributes = array())
  {
    $this->_nested[] = new HTMLElement($tag, $content, $attributes);

    return $this;
  }

  public function nestTagArray($tag, array $values, $attributes = array())
  {
    foreach($values as $value)
    {
      $this->nestTag($tag, $value, $attributes);
    }

    return $this;
  }

  public function nest(Renderable $item)
  {
    $this->_nested[] = $item;

    return $this;
  }

  public function renderAttributes()
  {
    $out = '';
    foreach($this->_attributes as $attr => $attr_v)
    {
      $out .= " $attr=\"$attr_v\"";
    }

    return $out;
  }

  public function render()
  {
    $return = empty($this->_tag) ? '' : '<' . $this->_tag . $this->renderAttributes() . '>';
    $return .= $this->_content;
    foreach($this->_nested as $nest)
    {
      if($nest instanceof Renderable)
      {
        $return .= $nest->render();
      }
    }
    $return .= empty($this->_tag) ? '' : '</' . $this->_tag . '>';

    return $return;
  }

  public function __toString()
  {
    return $this->render();
  }
}
