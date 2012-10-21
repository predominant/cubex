<?php
/**
 * User: brooke.bryan
 * Date: 17/10/12
 * Time: 22:30
 * Description:
 */

namespace Cubex\Http;

class Request extends \Cubex\Data\Handler
{

  const TYPE_AJAX = '_cubex_ajax_';
  const TYPE_FORM = '_cubex_form_';

  private $_path;
  private $_host;
  private $_subdomain;
  private $_domain;
  private $_tld;


  public function __construct($path = null, $host = null)
  {
    $this->_host = $host === null ? $_SERVER['HTTP_HOST'] : $host;//SERVER_NAME
    $this->_path = $path === null ? $_SERVER['REQUEST_URI'] : $path;
  }

  final public function setPath($path)
  {
    $this->_path = $path;

    return $this;
  }

  final public function getPath()
  {
    return $this->_path;
  }

  final public function setHost($host)
  {
    $this->_host = $host;

    return $this;
  }

  final public function getHost()
  {
    list($host) = explode(':', $this->_host, 2);

    return $host;
  }

  final public function processHost($host)
  {
    $host_parts       = explode('.', $host, 3);
    $spcount          = count($host_parts);
    $this->_subdomain = $spcount == 3 ? $host_parts[0] : '';
    $this->_domain    = $host_parts[$spcount - 2];
    $this->_tld       = $host_parts[$spcount - 1];

    return $this;
  }

  final public function getProtocol()
  {
    return $this->isHTTP() ? 'http://' : 'https://';
  }

  final public function getSubDomain()
  {
    if($this->_subdomain === null) $this->processHost($this->_host);

    return $this->_subdomain;
  }

  final public function getDomain()
  {
    if($this->_domain === null) $this->processHost($this->_host);

    return $this->_domain;
  }

  final public function getTld()
  {
    if($this->_tld === null) $this->processHost($this->_host);

    return $this->_tld;
  }

  final public function getRequestData()
  {
    return $this->_data;
  }

  final public function setRequestData(array $request_data)
  {
    $this->_data = $request_data;

    return $this;
  }

  public function getRemoteAddr()
  {
    return $_SERVER['REMOTE_ADDR'];
  }

  public function isHTTPS()
  {
    if(empty($_SERVER['HTTPS'])) return false;
    else if(!strcasecmp($_SERVER["HTTPS"], "off")) return false;
    else return true;
  }

  final public function isHTTP()
  {
    return !$this->isHTTPS();
  }

  final public function isHTTPPost()
  {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  public function isAjax()
  {
    return $this->getExists(self::TYPE_AJAX);
  }

  public function isForm()
  {
    return $this->getExists(self::TYPE_FORM);
  }
}
