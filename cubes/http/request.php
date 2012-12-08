<?php
/**
 * User: brooke.bryan
 * Date: 17/10/12
 * Time: 22:30
 * Description:
 */

namespace Cubex\Http;

use Cubex\Data\Handler;
use Cubex\Cubex;

class Request extends Handler
{

  const TYPE_AJAX     = '_cubex_ajax_';
  const TYPE_FORM     = '_cubex_form_';
  const NO_JAVASCRIPT = '__noscript__';

  private $_path;
  private $_host;
  private $_subdomain;
  private $_domain;
  private $_tld;
  private $_port;
  private $_processed_host;


  public function __construct($path = null, $host = null)
  {
    $this->_host = $host === null ? $_SERVER['HTTP_HOST'] : $host; //SERVER_NAME
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
    return $this->_host;
  }

  private function processHost($host)
  {
    if($this->_processed_host) return $this;
    $extra_tlds = Cubex::config("general")->getArr("tlds", array());
    $hard_tlds  = array('co', 'com', 'org', 'me', 'gov', 'net', 'edu');
    $parts      = \array_reverse(\explode('.', $host));

    if(\strstr($parts[0], ':') !== false)
    {
      list($parts[0], $this->_port) = \explode(':', $parts[0], 2);
    }

    foreach($parts as $i => $part)
    {
      if(empty($this->_tld))
      {
        $this->_tld = $part;
      }
      else if(empty($this->_domain))
      {
        if($i < 2
        && (\strlen($part) == 2
        || \in_array($part . '.' . $this->_tld, $extra_tlds)
        || \in_array($part, $hard_tlds))
        )
        {
          $this->_tld = $part . '.' . $this->_tld;
        }
        else
        {
          $this->_domain = $part;
        }
      }
      else
      {
        if(empty($this->_subdomain))
        {
          $this->_subdomain = $part;
        }
        else
        {
          $this->_subdomain = $part . '.' . $this->_subdomain;
        }
      }
    }

    $this->_processed_host = true;

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

  final public function getPort()
  {
    if($this->_port === null) $this->processHost($this->_host);

    return $this->_port;
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
    else if(!\strcasecmp($_SERVER["HTTPS"], "off")) return false;
    else return true;
  }

  public function variables()
  {
    $variables = array();
    foreach($_REQUEST as $k => $v)
    {
      if(\substr($k, 0, 2) !== '__') $variables[$k] = $v;
    }

    return $variables;
  }

  final public function isHTTP()
  {
    return !$this->isHTTPS();
  }

  final public function isHTTPPost()
  {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  //TODO: When pushing ajax request, include _cubex_ajax_ POST VAR
  public function isAjax()
  {
    return $this->getExists(self::TYPE_AJAX);
  }

  //TODO: When posting to a form, include _cubex_form_ POST VAR
  public function isForm()
  {
    return $this->getExists(self::TYPE_FORM);
  }

  public function jsSupport()
  {
    return !isset($_REQUEST[self::NO_JAVASCRIPT]);
  }
}
