<?php
/**
 * User: brooke.bryan
 * Date: 21/10/12
 * Time: 14:42
 * Description:
 */

namespace Cubex\Base;

class WebPage
{

  private $_title;
  private $_http_status;
  private $_meta;
  private $_captured;
  private $_captured_content;
  private $_view;

  public function setView(\Cubex\View\View $view)
  {
    $this->_view = $view;

    return $this;
  }

  public function controller()
  {
    return \Cubex\Cubex::core()->controller();
  }

  public function setHttpStatus($status = 200)
  {
    $this->_http_status = $status;

    return $this;
  }

  public function getHttpStatus()
  {
    return $this->_http_status;
  }

  public function getCharset()
  {
    return 'UTF-8';
  }

  public function getTitle()
  {
    return $this->_title;
  }

  public function setTitle($title)
  {
    $this->_title = $title;

    return $this;
  }

  public function getMeta($key = null)
  {
    if($key === null) return $this->_meta;
    else return $this->_meta[$key];
  }

  public function getMetaHTML()
  {
    if(!$this->_meta) return '';
    $html = '';
    foreach($this->_meta as $name => $content)
    {
      $html .= '<meta name="' . $name . '" content="' . $content . '" />';
    }

    return $html;
  }

  public function getHead()
  {
    //TODO: Get Stylesheets
    return $this->getMetaHTML();
  }

  public function getBody()
  {
    if($this->_view instanceof \Cubex\View\View)
    {
      return $this->_view->render();
    }
    else
    {
      $this->capturedContent();
    }
  }

  public function getClosing()
  {
    //TODO: Get Scripts
    return '';
  }

  public function render()
  {
    $this->preRender();
    $charset     = $this->getCharset();
    $title       = $this->getTitle();
    $head        = $this->getHead();
    $body        = $this->getBody();
    $closing     = $this->getClosing();
    $method      = strtoupper(isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : 'GET');
    $request_url = \Cubex\Cubex::request()->getPath();
    $request_url .= '?' . http_build_query(\Cubex\Cubex::request()->variables(), '', '&amp;');

    $noscript = '<meta http-equiv="refresh" content="0; URL=' . $request_url . '&amp;__noscript__=1" />';
    if(\Cubex\Cubex::request()->jsSupport() === false) $noscript = '';

    //TODO: Handle popups / ajax / form requests

    $response = <<<EOHTML
<!DOCTYPE html>
<html class="no_js"><head><meta charset="$charset" />
<script>function envPop(a){function b(c) {for (var d in a)c[d] = a[d];};window.Env = Env = window.Env || {};b(Env);};
!function(){document.documentElement.className.replace('no_js', '');}();
envPop({"method":"$method"});</script><noscript>{$noscript}</noscript>
<title>{$title}</title>{$head}</head><body>{$body}{$closing}</body></html>
EOHTML;

    return $response;

  }


  final public function beginCapture()
  {
    $this->_captured = false;
    ob_start();
  }

  final public function capturedContent()
  {
    return $this->_captured_content;
  }

  final public function endCapture()
  {
    if($this->_captured === false)
    {
      $this->_captured_content = ob_get_clean();
      $this->_captured         = true;
    }
    else $this->_captured = false;
  }

  final public function preRender()
  {
    if($this->_captured === false) $this->endCapture();
  }
}
