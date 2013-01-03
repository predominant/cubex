<?php
/**
 * User: brooke.bryan
 * Date: 17/10/12
 * Time: 22:30
 * Description:
 */

namespace Cubex\Http;

use Cubex\Cubex;
use Cubex\Traits\Data\Handler;

/**
 * Standard Request Handler
 */
class Request implements \IteratorAggregate
{
  use Handler;

  const TYPE_AJAX     = '_cubex_ajax_';
  const TYPE_FORM     = '_cubex_form_';
  const NO_JAVASCRIPT = '__noscript__';

  private $_path;
  private $_host;
  private $_subdomain;
  private $_domain;
  private $_tld;
  private $_port = 80;
  private $_processedHost;


  /**
   * @param null $path Defaults to $_SERVER['HTTP_HOST']
   * @param null $host Defaults to $_SERVER['REQUEST_URI']
   */
  public function __construct($path = null, $host = null)
  {
    $this->_host = $host === null ? $_SERVER['HTTP_HOST'] : $host; //SERVER_NAME
    $this->_path = $path === null ? $_SERVER['REQUEST_URI'] : $path;
  }

  /**
   * @param string $path
   *
   * @return Request
   */
  final public function setPath($path)
  {
    $this->_path = $path;

    return $this;
  }

  /**
   * @return string
   */
  final public function getPath()
  {
    return $this->_path;
  }

  /**
   * @param string $host
   *
   * @return Request
   */
  final public function setHost($host)
  {
    $this->_host = $host;

    return $this;
  }

  /**
   * @return string
   */
  final public function getHost()
  {
    return $this->_host;
  }

  /**
   * Convert the host to subdomain / domain / tld
   *
   * @param string $host
   *
   * @return Request
   */
  private function processHost($host)
  {
    if($this->_processedHost)
    {
      return $this;
    }
    $extraTlds = Cubex::config("general")->getArr("tlds", array());
    $hardTlds  = array('co', 'com', 'org', 'me', 'gov', 'net', 'edu');
    $parts     = \array_reverse(\explode('.', $host));

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
        || \in_array($part . '.' . $this->_tld, $extraTlds)
        || \in_array($part, $hardTlds))
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

    $this->_processedHost = true;

    return $this;
  }

  /**
   * http:// or https://
   *
   * @return string
   */
  final public function getProtocol()
  {
    return $this->isHTTP() ? 'http://' : 'https://';
  }

  /**
   * @return string|null
   */
  final public function getSubDomain()
  {
    if($this->_subdomain === null)
    {
      $this->processHost($this->_host);
    }

    return $this->_subdomain;
  }

  /**
   * @return string
   */
  final public function getDomain()
  {
    if($this->_domain === null)
    {
      $this->processHost($this->_host);
    }

    return $this->_domain;
  }

  /**
   * @return string
   */
  final public function getTld()
  {
    if($this->_tld === null)
    {
      $this->processHost($this->_host);
    }

    return $this->_tld;
  }

  /**
   * @return int
   */
  final public function getPort()
  {
    if($this->_port === null)
    {
      $this->processHost($this->_host);
    }

    return $this->_port;
  }

  /**
   * @return array
   */
  final public function getRequestData()
  {
    return $this->_data;
  }

  /**
   * @param array $requestData
   *
   * @return Request
   */
  final public function setRequestData(array $requestData)
  {
    $this->_data = $requestData;

    return $this;
  }

  /**
   * Client IP Address
   *
   * @return mixed
   */
  public function getRemoteAddr()
  {
    return $_SERVER['REMOTE_ADDR'];
  }

  /**
   * @return bool
   */
  public function isHTTPS()
  {
    if(empty($_SERVER['HTTPS']))
    {
      return false;
    }
    else if(!\strcasecmp($_SERVER["HTTPS"], "off"))
    {
      return false;
    }
    else return true;
  }

  /**
   * REQUEST Variables (Excluding __ prefixed used by Cubex)
   *
   * @return array
   */
  public function variables()
  {
    $variables = array();
    foreach($_REQUEST as $k => $v)
    {
      if(\substr($k, 0, 2) !== '__')
      {
        $variables[$k] = $v;
      }
    }

    return $variables;
  }

  /**
   * @return bool
   */
  final public function isHTTP()
  {
    return !$this->isHTTPS();
  }

  /**
   * @return bool
   */
  final public function isHTTPPost()
  {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  /**
   * @return bool
   */
  public function isAjax()
  {
    //TODO: When pushing ajax request, include _cubex_ajax_ POST VAR
    return $this->getExists(self::TYPE_AJAX);
  }

  /**
   * @return bool
   */
  public function isForm()
  {
    //TODO: When posting to a form, include _cubex_form_ POST VAR
    return $this->getExists(self::TYPE_FORM);
  }

  /**
   * @return bool
   */
  public function jsSupport()
  {
    return !isset($_REQUEST[self::NO_JAVASCRIPT]);
  }
}
