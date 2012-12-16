<?php
/**
 * User: brooke.bryan
 * Date: 21/11/12
 * Time: 19:23
 * Description:
 */

namespace Cubex\Http;

/**
 * Standard redirection handler
 */
class Redirect
{
  private $_url = null;
  private $_http_status = 200;
  private $_die_render = false;

  /**
   * @param      $url
   * @param int  $status
   * @param bool $die
   */
  public function __construct($url, $status = 302, $die = false)
  {
    $this->_url    = $url;
    $this->_http_status = $status;
    $this->_die_render  = $die;
  }

  /**
   * Set the HTTP Status Code for the redirect
   *
   * @param int $status
   *
   * @return Redirect
   */
  public function setHttpStatus($status=200)
  {
    $this->_http_status = $status;
    return $this;
  }

  /**
   * HTTP Status Code to be used
   *
   * @return int
   */
  public function getHttpStatus()
  {
    return $this->_http_status;
  }

  /**
   * URL to redirect the browser to
   *
   * @param $url
   *
   * @return Redirect
   */
  public function setUrl($url)
  {
    $this->_url = $url;
    return $this;
  }

  /**
   * URL the browser will be redirected to
   *
   * @return string
   */
  public function getUrl()
  {
    return $this->_url;
  }

  /**
   * Enable or Disable the kill switch after redirect
   *
   * @param bool $do
   *
   * @return Redirect
   */
  public function setDieRender($do=false)
  {
    $this->_die_render = $do;
    return $this;
  }

  /**
   * Will the redirect die once headers sent?
   * @return bool
   */
  public function getDieRender()
  {
    return $this->_die_render;
  }

  /**
   * Set the redirect headers and potential bail out
   *
   * @param bool $from_response
   */
  public function redirect($from_response = false)
  {
    if(!$from_response) \header('Status: ' . $this->_http_status);
    \header('Location: ' . $this->_url);
    if(!$from_response && $this->_die_render) die;
  }
}
