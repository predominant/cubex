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
use Cubex\Base\ErrorPage;

abstract class WebpageController extends Controller
{

  /**
   * @var \Cubex\Base\WebPage
   */
  protected $_webpage;
  /**
   * @var \Cubex\View\View
   */
  protected $_view;

  protected $_capture_nest = 'content';

  public function initialiseWebpage()
  {
    $this->_webpage = new WebPage();
    $this->_view    = $this->baseView();
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

  public function finaliseWebpage()
  {
    if(!$this->_view->isNested($this->_capture_nest))
    {
      $this->_view->nest($this->_capture_nest, $this->_webpage->capturedView());
    }
  }

  public function processRequest()
  {
    try
    {
      $this->initialiseWebpage();
      $response = $this->routeRequest();
      if(!$this->_delegated)
      {
        $this->finaliseWebpage();
        $response = $this->_webpage;
      }
    }
    catch(\Exception $e)
    {
      $webpage  = new ErrorPage(500, $e->getMessage(), array('path' => $this->request()->getPath()));
      $response = new Response($webpage);
    }

    return $response;
  }
}
