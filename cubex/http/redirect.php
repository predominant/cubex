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
  private $_httpStatus = 200;
  private $_dieRender = false;

  /**
   * @param      $url
   * @param int  $status
   * @param bool $die
   */
  public function __construct($url, $status = 302, $die = false)
  {
    $this->_url    = $url;
    $this->_httpStatus = $status;
    $this->_dieRender  = $die;
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
    $this->_httpStatus = $status;
    return $this;
  }

  /**
   * HTTP Status Code to be used
   *
   * @return int
   */
  public function getHttpStatus()
  {
    return $this->_httpStatus;
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
    $this->_dieRender = $do;
    return $this;
  }

  /**
   * Will the redirect die once headers sent?
   * @return bool
   */
  public function getDieRender()
  {
    return $this->_dieRender;
  }

  /**
   * Set the redirect headers and potential bail out
   *
   * @param bool $fromResponse
   */
  public function redirect($fromResponse = false)
  {
    if(!$fromResponse) \header('Status: ' . $this->_httpStatus);
    \header('Location: ' . $this->_url);
    if(!$fromResponse && $this->_dieRender) die;
  }
}
