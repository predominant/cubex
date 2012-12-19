<?php
/**
 * User: brooke.bryan
 * Date: 04/12/12
 * Time: 15:58
 * Description:
 */

namespace Cubex\View;

/**
 * Simple HTML Render
 */
class HTMLElement implements Renderable
{

  private $_tag = '';
  private $_nested = array();
  private $_attributes = array();
  private $_content = '';

  /**
   * @param string $tag
   * @param array  $attributes
   * @param string $content
   *
   * @return HTMLElement
   */
  public static function create($tag = '', $attributes = array(), $content = '')
  {
    return new HTMLElement($tag, $attributes, $content);
  }

  /**
   * @param string $tag
   * @param array  $attributes
   * @param string $content
   */
  public function __construct($tag = '', $attributes = array(), $content = '')
  {
    $this->_tag        = $tag;
    $this->_content    = $content;
    $this->_attributes = $attributes;
  }

  /**
   * @param $content
   *
   * @return HTMLElement
   */
  public function setContent($content)
  {
    $this->_content = $content;

    return $this;
  }

  /**
   * @param       $tag
   * @param array $attributes
   * @param       $content
   *
   * @return HTMLElement
   */
  public function nestElement($tag, $attributes = array(), $content = '')
  {
    $this->_nested[] = new HTMLElement($tag, $attributes, $content);

    return $this;
  }

  /**
   * @param       $tag
   * @param array $attributes
   * @param array $values
   *
   * @return HTMLElement
   */
  public function nestElements($tag, $attributes = array(), array $values = array())
  {
    foreach($values as $value)
    {
      $this->nestElement($tag, $attributes, $value);
    }

    return $this;
  }

  /**
   * @param Renderable $item
   *
   * @return HTMLElement
   */
  public function nest(Renderable $item)
  {
    $this->_nested[] = $item;

    return $this;
  }

  /**
   * @return string
   */
  public function renderAttributes()
  {
    $attributes = array();
    if($this->_attributes === null || !\is_array($this->_attributes))
    {
      return '';
    }

    foreach($this->_attributes as $attr => $attrV)
    {
      if($attrV === null)
      {
        continue;
      }
      $attributes[] = ' ' . $attr . '="' . self::escape($attrV) . '"';
    }

    return \implode(' ', $attributes);
  }

  /**
   * @return string
   */
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

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->render();
  }

  /**
   * @param $content
   *
   * @return string
   */
  public static function escape($content)
  {
    return \htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
  }
}
