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

class Template extends Handler implements Renderable
{

  const STATE_DYNAMIC     = 'dynamic';
  const STATE_PRECOMPILED = 'precompiled';

  private $_base = '';
  private $_nested = array();
  private $_render_file = null;
  private $_view_type = self::STATE_DYNAMIC;
  private $_compiled = '';
  public static $cache = array();
  public static $ephemeral = array();
  public static $last_known_base = '';

  public function __construct($file = null, $application = null)
  {
    if($application !== null && $application instanceof Application)
    {
      $this->setBasePath($application->filePath() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR);
    }
    if($file !== null)
    {
      $this->setTemplateFile($file);
    }
  }

  public function addEphemeral($name, $value)
  {
    self::$ephemeral[$name] = $value;
  }

  public function setBasePath($base)
  {
    $this->_base           = \substr($base, -1) != DIRECTORY_SEPARATOR ? $base . DIRECTORY_SEPARATOR : $base;
    self::$last_known_base = $this->_base;
  }

  public function getBasePath()
  {
    return empty($this->_base) ? self::$last_known_base : $this->_base;
  }

  final public function setTemplateFile($filepath, $ext = 'phtml')
  {
    $this->_render_file = $this->getBasePath() . $filepath . "." . $ext;
  }

  final public function isNested($name)
  {
    return isset($this->_nested[$name]);
  }

  final public function nest($name, Renderable $view)
  {
    $this->_nested[$name] = $view;
  }

  public function renderNest($name)
  {
    if(isset($this->_nested[$name]))
    {
      $nest = $this->_nested[$name];
      if($nest instanceof Renderable)
      {
        return $nest->render();
      }
    }
    return '';
  }

  final public function render($rerender = false)
  {
    if($rerender || $this->_view_type == self::STATE_DYNAMIC)
    {
      $rendered = '';

      if($this->_render_file !== null)
      {
        foreach(self::$ephemeral as $k => $v)
        {
          if(!isset($this->$k))
          {
            $this->$k = $v;
          }
        }

        $view_content = $this->loadRaw();
        \ob_start();
        try //Make sure the view does not cause the entire render to fail
        {
          /* Close PHP tags to allow for html and opening tags */
          eval('?>' . $view_content);
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

  public function setCompiled($compiled_output)
  {
    $this->_compiled  = $compiled_output;
    $this->_view_type = self::STATE_PRECOMPILED;

    return $this;
  }

  public function loadRaw()
  {
    if(isset(static::$cache[\md5($this->_render_file)]))
    {
      return static::$cache[\md5($this->_render_file)];
    }
    else
    {
      return static::$cache[\md5($this->_render_file)] = \file_get_contents($this->_render_file);
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

  /**
   * Translate string to locale
   *
   * @param $message string $string
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
