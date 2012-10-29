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
  private $_meta;

  public function redirect($url, $final = true)
  {
    $this->setHeader("location: " . $url);
    if($final) die;
  }

  public function setHeader($header, $replace = false)
  {
    if(!headers_sent())
    {
      header($header, $replace);

      return true;
    }

    return false;
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
    return '';
  }

  public function getClosing()
  {
    //TODO: Get Scripts
    return '';
  }

  public function render()
  {
    $charset     = $this->getCharset();
    $title       = $this->getTitle();
    $head        = $this->getHead();
    $body        = $this->getBody();
    $closing     = $this->getClosing();
    $method      = strtoupper(isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : 'GET');
    $request_url = \Cubex\Cubex::request()->getPath();
    $request_url .= '?' . http_build_query(\Cubex\Cubex::request()->variables(), '', '&amp;');

    $response = <<<EOHTML
<!DOCTYPE html>
<html class="no_js"><head><meta charset="$charset" />
<script>function envPop(a){function b(c) {for (var d in a)c[d] = a[d];};Env = window.Env || {};b(Env);};
!function(){var doc = document,htm = doc.documentElement;htm.className = htm.className.replace('no_js', '');}();
envPop({"method":"$method"});</script>
<noscript><meta http-equiv="refresh" content="0; URL=$request_url&amp;__noscript__=1" /></noscript>
<title>{$title}</title>{$head}</head><body>{$body}{$closing}</body></html>
EOHTML;

    return $response;

  }
}
