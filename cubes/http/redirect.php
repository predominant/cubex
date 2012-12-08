<?php
/**
 * User: brooke.bryan
 * Date: 21/11/12
 * Time: 19:23
 * Description:
 */

namespace Cubex\Http;

class Redirect
{
  private $_url = null;
  private $_http_status = 200;
  private $_die_render = false;

  public function __construct($url, $status = 302, $die = false)
  {
    $this->_url    = $url;
    $this->_http_status = $status;
    $this->_die_render  = $die;
  }

  public function setHttpStatus($status=200)
  {
    $this->_http_status = $status;
    return $this;
  }

  public function getHttpStatus()
  {
    return $this->_http_status;
  }

  public function setUrl($url)
  {
    $this->_url = $url;
    return $this;
  }

  public function getUrl()
  {
    return $this->_url;
  }

  public function setDieRender($do=false)
  {
    $this->_die_render = $do;
    return $this;
  }

  public function getDieRender()
  {
    return $this->_die_render;
  }

  public function redirect($from_response = false)
  {
    if(!$from_response) \header('Status: ' . $this->_http_status);
    \header('Location: ' . $this->_url);
    if(!$from_response && $this->_die_render) die;
  }
}
