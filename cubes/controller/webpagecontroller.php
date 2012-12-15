<?php
/**
 * User: brooke.bryan
 * Date: 08/12/12
 * Time: 10:27
 * Description:
 */

namespace Cubex\Controller;

use Cubex\Base\Controller;
use Cubex\Base\WebPage;
use Cubex\Http\Response;
use Cubex\View\Renderable;

abstract class WebpageController extends Controller
{

  /**
   * @var \Cubex\Base\WebPage
   */
  protected $_webpage;
  /**
   * @var \Cubex\View\Template
   */
  protected $_view;

  protected $_capture_nest = 'content';

  public function initialiseWebpage()
  {
    $this->_webpage = new WebPage();
    $this->_view    = $this->baseTemplate();
    $this->_webpage->setView($this->_view);
    $this->initialisedPage();
    $this->_webpage->beginCapture();
  }

  public function initialisedPage()
  {
    return true;
  }

  public function setTitle($title)
  {
    $this->_webpage->setTitle($title);
  }

  public function finaliseWebpage($response = null)
  {
    if(!$this->_view->isNested($this->_capture_nest))
    {
      if($response === null)
      {
        $this->_view->nest($this->_capture_nest, $this->_webpage->capturedView());
      }
      else if($response instanceof Renderable)
      {
        $this->_view->nest($this->_capture_nest, $response);
      }
    }
  }

  public function processRequest()
  {
    $this->initialiseWebpage();
    $response = $this->routeRequest();
    if(!$this->_delegated)
    {
      if($response instanceof Response)
      {
        return $response;
      }

      $this->finaliseWebpage($response);
      $response = $this->_webpage;
    }

    return $response;
  }
}
