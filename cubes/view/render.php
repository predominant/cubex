<?php
/**
 * User: brooke.bryan
 * Date: 17/12/12
 * Time: 21:46
 * Description:
 */
namespace Cubex\View;

/**
 * Basic render object
 */
class Render implements Renderable
{
  private $_content = '';

  /**
   * @param $content
   */
  public function __construct($content)
  {
    $this->setContent($content);
  }

  /**
   * @return string
   */
  public function __tostring()
  {
    return $this->render();
  }

  /**
   * @param $content
   */
  public function setContent($content)
  {
    $this->_content = $content;
  }

  /**
   * @return string
   */
  public function render()
  {
    return $this->_content;
  }
}
