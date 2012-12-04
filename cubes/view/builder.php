<?php
/**
 * User: brooke.bryan
 * Date: 04/12/12
 * Time: 15:58
 * Description:
 */

namespace Cubex\View;

class Builder implements Renderable
{

  private $_tag = '';
  private $_nested = array();
  private $_content = '';

  public static function create($tag = '', $content = '')
  {
    return new Builder($tag, $content);
  }

  public function __construct($tag = '', $content = '')
  {
    $this->_tag     = $tag;
    $this->_content = $content;
  }

  public function setContent($content)
  {
    $this->_content = $content;

    return $this;
  }

  public function nestTag($tag, $content)
  {
    $this->_nested[] = new Builder($tag, $content);

    return $this;
  }

  public function nestTagArray($tag, array $values)
  {
    foreach($values as $value)
    {
      $this->nestTag($tag, $value);
    }

    return $this;
  }

  public function nest(Renderable $item)
  {
    $this->_nested[] = $item;

    return $this;
  }

  public function render()
  {
    $return = empty($this->_tag) ? '' : '<' . $this->_tag . '>';
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
