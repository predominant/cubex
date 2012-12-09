<?php
/**
 * User: brooke.bryan
 * Date: 21/11/12
 * Time: 18:53
 * Description:
 */

namespace Cubex\Http;
use \Cubex\Base\WebPage;

class Response
{

  /**
   * @var Redirect
   */
  protected $_redirect = null;
  protected $_http_status = 200;
  /**
   * @var Webpage
   */
  protected $_webpage = null;
  protected $_render_type = null;
  protected $_die_render = false;
  protected $_rendered = false;

  const RENDER_WEBPAGE  = 'webpage';
  const RENDER_REDIRECT = 'redirect';

  public function __construct($source = null)
  {
    if($source instanceof WebPage)
    {
      $this->webpage($source);
    }
    else if($source instanceof Redirect)
    {
      $this->redirect($source);
    }
  }

  public function webpage(WebPage $page)
  {
    $this->_webpage     = $page;
    $this->_http_status = $page->getHttpStatus();
    $this->_render_type = self::RENDER_WEBPAGE;
  }

  public function redirect(Redirect $redirect)
  {
    $this->_redirect    = $redirect;
    $this->_http_status = $redirect->getHttpStatus();
    $this->_die_render  = $redirect->getDieRender();
    $this->_render_type = self::RENDER_REDIRECT;
  }

  public function render()
  {
    if(!\headers_sent()) \header('Status: ' . $this->_http_status);

    switch($this->_render_type)
    {
      case self::RENDER_WEBPAGE;
        if($this->_webpage instanceof WebPage)
        {
          /* Render header before content to allow browser to start loading css */
          \ob_implicit_flush(true);
          echo $this->_webpage->renderHead();
          \ob_flush();
          echo $this->_webpage->renderBody();
          \ob_flush();
          echo $this->_webpage->renderClosing();
          \ob_flush();
        }
        break;
      case self::RENDER_REDIRECT:
        if($this->_redirect instanceof Redirect)
        {
          $this->_redirect->redirect();
        }
        break;
    }

    $this->_rendered = true;

    if($this->_die_render) die;
  }

  public function sendHeader($header, $replace = false)
  {
    if(!\headers_sent())
    {
      \header($header, $replace);

      return true;
    }

    return false;
  }

  public function __destruct()
  {
    if(!$this->_rendered) $this->render();
  }
}
