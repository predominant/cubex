<?php
/**
 * User: brooke.bryan
 * Date: 21/11/12
 * Time: 18:53
 * Description:
 */

namespace Cubex\Http;

use \Cubex\Response\WebPage;
use Cubex\View\Renderable;

/**
 * Base response for dealing with HTTP requests
 */
class Response
{
  private $_headers = array();

  protected $_source = null;

  protected $_httpStatus = 200;
  protected $_renderType = null;

  /**
   * Number of seconds to cache response
   *
   * @var bool|int
   */
  protected $_cacheable = false;
  /**
   * Last modified timestamp
   *
   * @var bool|int
   */
  protected $_lastModified = false;

  protected $_rendered = false;

  const RENDER_WEBPAGE    = 'webpage';
  const RENDER_REDIRECT   = 'redirect';
  const RENDER_RENDERABLE = 'renderable';
  const RENDER_JSON       = 'json';
  const RENDER_TEXT       = 'text';
  const RENDER_UNKNOWN    = 'unknown';

  /**
   * Create a new response object with a generic render type
   * Rendering of unsupported items will throw exceptions
   *
   * @param null $source
   */
  public function __construct($source = null)
  {
    $this->addHeader("X-Powered-By", "Cubex");
    $this->addHeader("X-Frame-Options", "deny");
    $this->fromSource($source);
  }

  public function fromSource($source)
  {
    if($source instanceof WebPage)
    {
      $this->webpage($source);
    }
    else if($source instanceof Redirect)
    {
      $this->redirect($source);
    }
    else if($source instanceof Renderable)
    {
      $this->renderable($source);
    }
    else if(is_scalar($source))
    {
      $this->text($source);
    }
    else if($source !== null)
    {
      $this->_source     = $source;
      $this->_renderType = self::RENDER_UNKNOWN;
    }

    return $this;
  }

  /**
   * Returns the current render type of the response
   *
   * @return null|string
   */
  public function getRenderType()
  {
    return $this->_renderType;
  }

  /**
   * Set a header to be sent to the client on respond
   *
   * @param string       $header
   * @param string       $data
   * @param bool         $replace
   *
   * @return Response
   */
  public function addHeader($header, $data, $replace = true)
  {
    if(!$replace)
    {
      foreach($this->_headers as $h)
      {
        if(\strtolower($h[0]) == \strtolower($header))
        {
          return $this;
        }
      }
    }
    $this->_headers[] = array($header, $data, $replace);

    return $this;
  }

  /**
   * Set the response to be plain text output
   *
   * @param $text
   *
   * @return Response
   */
  public function text($text)
  {
    $this->_source     = $text;
    $this->_renderType = self::RENDER_TEXT;

    return $this;
  }


  /**
   * Set the response to be a json encoded object
   *
   * @param $object
   *
   * @return Response
   */
  public function json($object)
  {
    $this->_source     = $object;
    $this->_renderType = self::RENDER_JSON;

    return $this;
  }

  /**
   * Set the response to be a renderable object
   *
   * @param \Cubex\View\Renderable $item
   *
   * @return Response
   */
  public function renderable(Renderable $item)
  {
    $this->_source     = $item;
    $this->_renderType = self::RENDER_RENDERABLE;

    return $this;
  }

  /**
   * Set the response to be a web page result
   *
   * @param \Cubex\Response\WebPage $page
   *
   * @return Response
   */
  public function webpage(WebPage $page)
  {
    $this->_source     = $page;
    $this->_httpStatus = $page->getHttpStatus();
    $this->_renderType = self::RENDER_WEBPAGE;

    return $this;
  }

  /**
   * Set the response to be a redirect response
   *
   * @param Redirect $redirect
   *
   * @return Response
   */
  public function redirect(Redirect $redirect)
  {
    $this->_source     = $redirect;
    $this->_httpStatus = $redirect->getHttpStatus();
    $this->_renderType = self::RENDER_REDIRECT;

    return $this;
  }

  /**
   * Send a response to the client based on the constructed response object
   * Only the most recent response initiator/call will be used
   *
   * @throws \Exception
   * @return Response
   */
  public function respond()
  {
    $this->addHeader("X-Cubex-Render", $this->_renderType);
    $this->addHeader("Status", $this->_httpStatus);

    switch($this->_renderType)
    {
      case self::RENDER_WEBPAGE;
        $this->addHeader("Content-Type", "text/html; charset=UTF-8", false);
        $this->sendHeaders();

        if($this->_source instanceof WebPage)
        {
          /* Render header before content to allow browser to start loading css */
          \ob_implicit_flush(true);
          echo $this->_source->renderHead();
          echo $this->_source->renderBody();
          echo $this->_source->renderClosing();
        }
        break;
      case self::RENDER_REDIRECT:

        $this->sendHeaders();

        if($this->_source instanceof Redirect)
        {
          $this->_source->redirect();
          if($this->_source->getDieRender())
          {
            die;
          }
        }
        break;
      case self::RENDER_RENDERABLE:

        $this->addHeader("Content-Type", "text/html; charset=UTF-8", false);
        $this->sendHeaders();

        /* Render header before content to allow browser to start loading css */
        \ob_implicit_flush(true);
        if($this->_source instanceof Renderable)
        {
          echo $this->_source->render();
        }
        break;
      case self::RENDER_JSON:

        $this->addHeader("Content-Type", "application/json", false);
        $this->sendHeaders();

        $response = \json_encode($this->_source);

        // Prevent content sniffing attacks by encoding "<" and ">", so browsers
        // won't try to execute the document as HTML
        $response = \str_replace(array('<', '>'), array('\u003c', '\u003e'), $response);

        echo $response;

        break;
      case self::RENDER_TEXT:

        $this->addHeader("Content-Type", "text/plain", false);
        $this->sendHeaders();
        echo $this->_source;

        break;
      default:
        throw new \Exception("Unsupported response type");
        break;
    }

    $this->_rendered = true;

    return $this;
  }

  /**
   * Send headers to client
   *
   * @return Response
   */
  public function sendHeaders()
  {
    if(!\headers_sent())
    {
      \header("HTTP/1.0 " . $this->_httpStatus . ' ' . $this->getStatusReason());

      if($this->_lastModified)
      {
        $this->addHeader('Last-Modified', $this->httpHeaderDate($this->_lastModified));
      }

      if($this->_cacheable)
      {
        $this->addHeader("Expires", $this->httpHeaderDate(time() + $this->_cacheable));
      }
      else
      {
        //Force no cache | Mayan EOW
        $this->addHeader("Expires", "Fri, 21 Dec 2012 11:11:11 GMT");
        $this->addHeader("Pragma", "no-cache");
        $this->addHeader("Cache-Control", "private, no-cache, no-store, must-revalidate");
      }

      foreach($this->_headers as $header)
      {
        \header($header[0] . ":" . $header[1], $header[2]);
      }
    }

    return $this;
  }

  /**
   * Set cacheable time in seconds
   *
   * @param int $seconds
   *
   * @return Response
   */
  public function setCacheable($seconds = 3600)
  {
    $this->_cacheable = $seconds;

    return $this;
  }

  /**
   * Disable response cache
   *
   * @return Response
   */
  public function disbleCache()
  {
    $this->_cacheable = false;

    return $this;
  }

  /**
   * Set the last modified time of the respones
   * Useful when returning static elements to improve cache
   *
   * @param int $timestamp
   *
   * @return Response
   */
  public function setLastModified($timestamp = 0)
  {
    $this->_lastModified = $timestamp;

    return $this;
  }

  /**
   * Unset a last modified timestamp
   *
   * @return Response
   */
  public function unsetLastModified()
  {
    $this->_lastModified = false;

    return $this;
  }

  /**
   * Set HTTP status code
   *
   * @param $code
   */
  public function setStatus($code)
  {
    $this->_httpStatus = $code;
  }

  /**
   * HTTP Status Reasons
   *
   * @return string
   */
  protected function getStatusReason()
  {
    if($this->_httpStatus === null)
    {
      $this->_httpStatus = 200;
    }

    $reasons = array(
      // INFORMATIONAL CODES
      100 => 'Continue',
      101 => 'Switching Protocols',
      102 => 'Processing',
      // SUCCESS CODES
      200 => 'OK',
      201 => 'Created',
      202 => 'Accepted',
      203 => 'Non-Authoritative Information',
      204 => 'No Content',
      205 => 'Reset Content',
      206 => 'Partial Content',
      207 => 'Multi-status',
      208 => 'Already Reported',
      // REDIRECTION CODES
      300 => 'Multiple Choices',
      301 => 'Moved Permanently',
      302 => 'Found',
      303 => 'See Other',
      304 => 'Not Modified',
      305 => 'Use Proxy',
      307 => 'Temporary Redirect',
      // CLIENT ERROR
      400 => 'Bad Request',
      401 => 'Unauthorized',
      402 => 'Payment Required',
      403 => 'Forbidden',
      404 => 'Not Found',
      405 => 'Method Not Allowed',
      406 => 'Not Acceptable',
      407 => 'Proxy Authentication Required',
      408 => 'Request Time-out',
      409 => 'Conflict',
      410 => 'Gone',
      411 => 'Length Required',
      412 => 'Precondition Failed',
      413 => 'Request Entity Too Large',
      414 => 'Request-URI Too Large',
      415 => 'Unsupported Media Type',
      416 => 'Requested range not satisfiable',
      417 => 'Expectation Failed',
      418 => 'I\'m a teapot',
      422 => 'Unprocessable Entity',
      423 => 'Locked',
      424 => 'Failed Dependency',
      425 => 'Unordered Collection',
      426 => 'Upgrade Required',
      428 => 'Precondition Required',
      429 => 'Too Many Requests',
      431 => 'Request Header Fields Too Large',
      // SERVER ERROR
      500 => 'Internal Server Error',
      501 => 'Not Implemented',
      502 => 'Bad Gateway',
      503 => 'Service Unavailable',
      504 => 'Gateway Time-out',
      505 => 'HTTP Version not supported',
      506 => 'Variant Also Negotiates',
      507 => 'Insufficient Storage',
      508 => 'Loop Detected',
      511 => 'Network Authentication Required',
    );

    return isset($reasons[$this->_httpStatus]) ? $reasons[$this->_httpStatus] : '';
  }

  /**
   * Convert timestamp to a HTTP Header friendly format
   *
   * @param $timestamp
   *
   * @return string
   */
  public function httpHeaderDate($timestamp)
  {
    return \gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
  }
}
