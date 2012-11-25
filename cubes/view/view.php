<?php
/**
 * User: brooke.bryan
 * Date: 21/11/12
 * Time: 18:35
 * Description:
 */

namespace Cubex\View;

class View extends \Cubex\Data\Handler
{
  const VIEW_DYNAMIC     = 'dynamic';
  const VIEW_PRECOMPILED = 'precompiled';

  private $_base = '';
  private $_nested_views = array();
  private $_render_file = null;
  private $_view_type = self::VIEW_DYNAMIC;
  private $_compiled = '';
  public static $cache = array();
  public static $ephemeral = array();
  public static $last_known_base = '';

  public function __construct($file=null,$application=null)
  {
    if($application instanceof \Cubex\Application\Complex\Application)
    {
      $this->setBasePath($application->filePath() . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR);
    }
    if($file !== null)
    {
      $this->setViewFile($file);
    }
  }

  public function addEphemeral($name,$value)
  {
    self::$ephemeral[$name] = $value;
  }

  public function setBasePath($base)
  {
    $this->_base = substr($base,-1) != DIRECTORY_SEPARATOR ? $base . DIRECTORY_SEPARATOR : $base;
    self::$last_known_base = $this->_base;
  }

  public function getBasePath()
  {
    return empty($this->_base) ? self::$last_known_base : $this->_base;
  }

  final public function setViewFile($filepath,$ext = 'phtml')
  {
    $this->_render_file = $this->getBasePath() . $filepath . "." . $ext;
  }

  final public function nest($name, View $view)
  {
    $this->_nested_views[$name] = $view;
  }

  final public function render($rerender = false)
  {
    if($rerender || $this->_view_type == self::VIEW_DYNAMIC)
    {
      $rendered = '';
      foreach($this->_nested_views as $named => $nest)
      {
        if($nest instanceof View)
        {
          $$named = $nest->render();
        }
      }

      if($this->_render_file !== null)
      {

        foreach(self::$ephemeral as $k => $v)
        {
          if(!isset($this->$k))
          {
            $this->$k = $v;
          }
        }

        $view_content = $this->loadRawView();
        ob_start();
        try //Make sure the view does not cause the entire render to fail
        {
          /* Close PHP tags to allow for html and opening tags */
          eval('?>' . $view_content);
        }
        catch(\Exception $e)
        {
          ob_get_clean();
        }

        $rendered = ob_get_clean();
      }

      $this->setOutput($rendered);

      return $rendered;
    }

    return $this->_compiled;
  }

  public function setOutput($compiled_output)
  {
    $this->_compiled  = $compiled_output;
    $this->_view_type = self::VIEW_PRECOMPILED;

    return $this;
  }

  public function loadRawView()
  {
    if(isset(static::$cache[md5($this->_render_file)]))
    {
      return static::$cache[md5($this->_render_file)];
    }
    else
    {
      return static::$cache[md5($this->_render_file)] = file_get_contents($this->_render_file);
    }
  }
}