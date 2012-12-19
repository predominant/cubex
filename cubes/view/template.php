<?php
/**
 * User: brooke.bryan
 * Date: 21/11/12
 * Time: 18:35
 * Description:
 */

namespace Cubex\View;

use Cubex\Data\Handler;
use Cubex\Base\Application;
use Cubex\Language\Translatable;
use Cubex\Dispatch\Dispatcher;

class Template extends Handler implements Renderable
{

  const STATE_DYNAMIC     = 'dynamic';
  const STATE_PRECOMPILED = 'precompiled';

  private $_base = '';
  private $_nested = array();
  private $_renderFile = null;
  private $_viewType = self::STATE_DYNAMIC;
  private $_compiled = '';
  public static $cache = array();
  public static $ephemeral = array();
  public static $lastKnownBase = '';
  /**
   * @var \Cubex\Dispatch\Dispatcher
   */
  private $_dispatch;

  public function __construct($file = null, $base = null)
  {
    if($base !== null && $base instanceof Translatable)
    {
      $this->setBasePath($base->filePath() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR);
    }
    else if($base !== null)
    {
      if(is_dir($base))
      {
        $this->setBasePath($base);
      }
    }

    if($file !== null)
    {
      $this->setTemplateFile($file);
    }

    $this->setDispatcher(Application::$app);
  }

  public function setDispatcher(Dispatcher $dispatch)
  {
    $this->_dispatch = $dispatch;
  }

  public function addEphemeral($name, $value)
  {
    self::$ephemeral[$name] = $value;
  }

  public function setBasePath($base)
  {
    $this->_base           = \substr($base, -1) != DIRECTORY_SEPARATOR ? $base . DIRECTORY_SEPARATOR : $base;
    self::$lastKnownBase = $this->_base;
  }

  public function getBasePath()
  {
    return empty($this->_base) ? self::$lastKnownBase : $this->_base;
  }

  final public function setTemplateFile($filepath, $ext = 'phtml')
  {
    $this->_renderFile = $this->getBasePath() . $filepath . "." . $ext;
  }

  final public function isNested($name)
  {
    return isset($this->_nested[$name]);
  }

  final public function nest($name, Renderable $view)
  {
    $this->_nested[$name] = $view;
  }

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

  final public function render($rerender = false)
  {
    if($rerender || $this->_viewType == self::STATE_DYNAMIC)
    {
      $rendered = '';

      if($this->_renderFile !== null)
      {
        foreach(self::$ephemeral as $k => $v)
        {
          if(!isset($this->$k))
          {
            $this->$k = $v;
          }
        }

        $viewContent = $this->loadRaw();
        \ob_start();
        try //Make sure the view does not cause the entire render to fail
        {
          /* Close PHP tags to allow for html and opening tags */
          eval('?>' . $viewContent);
        }
        catch(\Exception $e)
        {
          \ob_get_clean();
        }

        $rendered = \ob_get_clean();
      }

      $this->setCompiled($rendered);

      return $rendered;
    }

    return $this->_compiled;
  }

  public function setCompiled($compiledOutput)
  {
    $this->_compiled  = $compiledOutput;
    $this->_viewType = self::STATE_PRECOMPILED;

    return $this;
  }

  public function loadRaw()
  {
    if(isset(static::$cache[\md5($this->_renderFile)]))
    {
      return static::$cache[\md5($this->_renderFile)];
    }
    else
    {
      return static::$cache[\md5($this->_renderFile)] = \file_get_contents($this->_renderFile);
    }
  }

  public function __toString()
  {
    try
    {
      $output = $this->render();

      return $output;
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
  }

  public function requireCss($file)
  {
    $this->_dispatch->requireCss($file);
    return $this;
  }

  public function requireJs($file)
  {
    $this->_dispatch->requireJs($file);
    return $this;
  }

  public function imgUri($file)
  {
    return $this->_dispatch->imgUri($file);
  }

  /**
   * Translate string to locale
   *
   * @param $message string $string
   *
   * @return string
   */
  public function t($message)
  {
    return Application::$app->t($message);
  }

  /**
   * Translate plural
   *
   * @param      $singular
   * @param null $plural
   * @param int  $number
   *
   * @return string
   */
  public function p($singular, $plural = null, $number = 0)
  {
    return Application::$app->p($singular, $plural, $number);
  }

  /**
   *
   * Translate plural, converting (s) to '' or 's'
   *
   */
  public function tp($text, $number)
  {
    return Application::$app->tp($text, $number);
  }
}
