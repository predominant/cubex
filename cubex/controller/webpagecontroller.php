<?php
/**
 * User: brooke.bryan
 * Date: 08/12/12
 * Time: 10:27
 * Description:
 */

namespace Cubex\Controller;

use Cubex\Response\WebPage;
use Cubex\Http\Response;
use Cubex\View\Renderable;

/**
 * Standardised Webpage Controller
 */
abstract class WebpageController extends BaseController
{

  /**
   * @var \Cubex\Response\WebPage
   */
  protected $_webpage;
  /**
   * @var \Cubex\Application\Layout
   */
  protected $_view;

  protected $_captureNest = 'content';

  public function initialiseWebpage()
  {
    $this->_webpage = new WebPage();
    $this->_view = $this->currentLayout();
    $this->_webpage->setView($this->_view);
    $this->initialisedPage();
    $this->_webpage->beginCapture();
  }

  /**
   * Hook for when the webpage has had its view set
   *
   * @return bool
   */
  public function initialisedPage()
  {
    return true;
  }

  /**
   * Set Page Title
   *
   * @param $title
   */
  public function setTitle($title)
  {
    $this->_webpage->setTitle($title);
  }

  /**
   * Capture view and nest into page
   *
   * @param null $response
   */
  public function finaliseWebpage($response = null)
  {
    if(!$this->_view->isNested($this->_captureNest))
    {
      if($response === null)
      {
        $this->_view->nest($this->_captureNest, $this->_webpage->capturedData());
      }
      else if($response instanceof Renderable)
      {
        $this->_view->nest($this->_captureNest, $response);
      }
    }
  }

  /**
   * Build the webpage return after processing actions
   *
   * @return \Cubex\Response\WebPage|\Cubex\Http\Response
   */
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
