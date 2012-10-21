<?php
/**
 * User: brooke.bryan
 * Date: 21/10/12
 * Time: 14:42
 * Description:
 */

namespace Cubex\Base;

class WebPage
{
  private $_title;
  private $_meta;

  public function redirect($url, $final = true)
  {
    $this->setHeader("location: " . $url);
    if($final) die;
  }

  public function setHeader($header, $replace = false)
  {
    if(!headers_sent())
    {
      header($header, $replace);

      return true;
    }

    return false;
  }

  public function getCharset()
  {
    return 'UTF-8';
  }

  public function getTitle()
  {
    return $this->_title;
  }

  public function setTitle($title)
  {
    $this->_title = $title;

    return $this;
  }

  public function getMeta($key = null)
  {
    if($key === null) return $this->_meta;
    else return $this->_meta[$key];
  }

  public function getMetaHTML()
  {
    if(!$this->_meta) return '';
    $html = '';
    foreach($this->_meta as $name => $content)
    {
      $html .= '<meta name="' . $name . '" content="' . $content . '" />';
    }
    return $html;
  }

  public function getHead()
  {
    //TODO: Get Stylesheets
    //TODO: Get Scripts
    return $this->getMetaHTML();
  }

  public function getBody()
  {
    return '';
  }

  public function render()
  {
    $charset = $this->getCharset();
    $title = $this->getTitle();
    $head  = $this->getHead();
    $body  = $this->getBody();

    $response = <<<EOHTML
<!DOCTYPE html>
<html>
  <head>
    <meta charset="$charset" />
    <title>{$title}</title>
    {$head}
  </head>
  <body>
  {$body}
  </body>
</html>

EOHTML;

    return $response;

  }
}
