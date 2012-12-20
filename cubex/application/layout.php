<?php
/**
 * User: brooke.bryan
 * Date: 20/12/12
 * Time: 14:27
 * Description:
 */
namespace Cubex\Application;

use Cubex\View\TemplatedView;
use Cubex\Project\Application;
use Cubex\View\HTMLElement;
use Cubex\View\Renderable;

/**
 * Standard application layout view
 */
class Layout extends TemplatedView
{
  private $_nested = array();
  private $_layout_template = 'default';

  /**
   * Set new template file for layout
   *
   * @param string $fileName
   */
  public function setLayoutTemplate($fileName = 'default')
  {
    $this->_layout_template = $fileName;
  }

  /**
   * Build default
   *
   * @return string
   */
  public function render()
  {
    if($this->_renderFolder === null)
    {
      $this->setTemplatesPath(
        Application::getApp()->filePath() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'layout'
      );
    }
    $this->setTemplateFile($this->_layout_template);
    return parent::render();
  }

  /**
   * @param $name
   *
   * @return bool
   */
  final public function isNested($name)
  {
    return isset($this->_nested[$name]);
  }

  /**
   * @param                        $name
   * @param \Cubex\View\Renderable $view
   */
  final public function nest($name, Renderable $view)
  {
    $this->_nested[$name] = $view;
  }

  /**
   * @param      $name
   * @param bool $containDivId
   *
   * @return string
   */
  public function renderNest($name, $containDivId = true)
  {
    $rendered = '';
    if(isset($this->_nested[$name]))
    {
      $nest = $this->_nested[$name];
      if($nest instanceof Renderable)
      {
        $rendered = $nest->render();
      }
    }

    if($containDivId === false)
    {
      return $rendered;
    }
    else
    {
      if(\is_string($containDivId))
      {
        $name = $containDivId;
      }

      return HTMLElement::create('div', array('id' => $name), $rendered)->render();
    }
  }
}