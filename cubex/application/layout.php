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
  /**
   * @var Application
   */
  private $_app;
  private $_nested = array();
  protected $_layoutTemplate = 'default';

  protected $_renderHooks = array('before' => array(), 'after' => array());

  public function __construct(Application $app)
  {
    $this->_app = $app;
  }

  public function setApp(Application $app)
  {
    $this->_app = $app;
    return $this;
  }

  public function getApp()
  {
    return $this->_app;
  }

  /**
   * Set new templates file for layout
   *
   * @param string $fileName
   */
  public function setLayoutTemplate($fileName = 'default')
  {
    $this->_layoutTemplate = $fileName;
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
        $this->getApp()->filePath() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'layout'
      );
    }
    $this->setTemplateFile($this->_layoutTemplate);
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

    if(isset($this->_renderHooks['before'][$name]))
    {
      foreach($this->_renderHooks['before'][$name] as $renderHook)
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

    if(isset($this->_renderHooks['after'][$name]))
    {
      foreach($this->_renderHooks['after'][$name] as $renderHook)
      {
        if($renderHook instanceof Renderable)
        {
          $rendered .= $renderHook->render();
        }
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

    return $rendered;
  }

  /**
   * @param                        $when
   * @param                        $nest
   * @param \Cubex\View\Renderable $render
   */
  protected function hookRender($when, $nest, Renderable $render)
  {
    $this->_renderHooks[$when][$nest][] = $render;
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
