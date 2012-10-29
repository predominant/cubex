<?php
/**
 * User: brooke.bryan
 * Date: 21/10/12
 * Time: 14:42
 * Description:
 */

namespace Cubex\Base;

class ErrorPage extends WebPage
{
  private $_params;

  public function __construct($code=404,$message="Page Not Found",$params=null)
  {
    $this->setTitle($code . ": " . $message);
    $this->_params = $params;
  }

  public function getBody()
  {
    $response = '';
    if(is_array($this->_params))
    {
      foreach($this->_params as $k => $v)
      {
        $response .= "$k = $v\n<br/>";
      }
    }
    return $response;
  }
}