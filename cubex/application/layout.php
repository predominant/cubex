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

  protected $_render_hooks = array('before' => array(), 'after' => array());

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

    if(isset($this->_render_hooks['before'][$name]))
    {
      foreach($this->_render_hooks['before'][$name] as $renderHook)
      {
        if($renderHook instanceof Renderable)
        {
          $rendered .= $renderHook->render();
        }
      }
    }

    if(isset($this->_nested[$name]))
    {
      $nest = $this->_nested[$name];
      if($nest instanceof Renderable)
      {
        $rendered = $nest->render();
      }
    }

    if($containDivId !== false)
    {
      if(\is_string($containDivId))
      {
        $name = $containDivId;
      }

      $rendered = HTMLElement::create('div', array('id' => $name), $rendered)->render();
    }

    if(isset($this->_render_hooks['after'][$name]))
    {
      foreach($this->_render_hooks['after'][$name] as $renderHook)
      {
        if($renderHook instanceof Renderable)
        {
          $rendered .= $renderHook->render();
        }
      }
    }

    return $rendered;
  }

  /**
   * @param                        $when
   * @param                        $nest
   * @param \Cubex\View\Renderable $render
   */
  protected function hookRender($when, $nest, Renderable $render)
  {
    $this->_render_hooks[$when][$nest][] = $render;
  }

  /**
   * @param                        $nest
   * @param \Cubex\View\Renderable $render
   *
   * @return Layout
   */
  public function nestBefore($nest, Renderable $render)
  {
    $this->hookRender("before", $nest, $render);
    return $this;
  }

  /**
   * @param                        $nest
   * @param \Cubex\View\Renderable $render
   *
   * @return Layout
   */
  public function nestAfter($nest, Renderable $render)
  {
    $this->hookRender("after", $nest, $render);
    return $this;
  }
}
