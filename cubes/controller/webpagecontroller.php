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
    $this->_webpage->beginCapture();
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
    $this->initialiseWebpage();
    if(!$this->routeRequest())
    {
      return "Your request could not be routed";
    }
    $this->finaliseWebpage();

    return new Response($this->_webpage);
  }
}
