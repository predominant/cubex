<?php
/**
 * User: brooke.bryan
 * Date: 21/10/12
 * Time: 14:42
 * Description:
 */

namespace Cubex\Response;

use Cubex\View\HTMLElement;

class ErrorPage extends WebPage
{

  private $_params;

  public function __construct($code = 404, $message = "Page Not Found", $params = null)
  {
    $this->setHttpStatus($code);
    $this->setTitle($code . ": " . $message);
    $this->_params = $params;
  }

  public function getBody()
  {
    $response = parent::getBody();
    if(\is_array($this->_params))
    {
      foreach($this->_params as $k => $v)
      {
        $response .= "$k = $v\n<br/>";
      }
    }

    return HTMLElement::create('', [], $response);
  }
}
