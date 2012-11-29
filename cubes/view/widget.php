<?php
/**
 * User: brooke.bryan
 * Date: 13/11/12
 * Time: 08:59
 * Description:
 */
namespace Cubex\View;

class Widget extends \Cubex\Base\Translatable implements Renderable
{
  protected $_captured;
  protected $_content;
  protected $_meta;

  public function __construct($capture = false)
  {
    if($capture) $this->begin();
  }

  final public function begin()
  {
    $this->_captured = false;
    ob_start();
  }

  final public function getCapturedContent()
  {
    return $this->_content;
  }

  final public function capture()
  {
    if($this->_captured === false)
    {
      $this->_content  = ob_get_clean();
      $this->_captured = true;
    }
    else $this->_captured = false;
  }

  final public function preRender()
  {
    if($this->_captured === false) $this->capture();
  }

  public function render()
  {
    $this->preRender();
    return $this->_content;
  }

  public function setMeta($key, $value)
  {
    $this->_meta[$key] = $value;
  }

  public function getMeta($key, $default = null)
  {
    return isset($this->_meta[$key]) ? $this->_meta[$key] : $default;
  }
}
