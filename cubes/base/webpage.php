<?php
/**
 * User: brooke.bryan
 * Date: 21/10/12
 * Time: 14:42
 * Description:
 */

namespace Cubex\Base;

use \Cubex\Cubex;
use Cubex\View\Renderable;
use Cubex\View\Template;
use Cubex\Dispatch\Prop;
use Cubex\View\Partial;
use Cubex\View\Render;

/**
 * Standard webpage response builder
 */
class WebPage
{

  private $_title;
  private $_http_status = 200;
  private $_meta;
  private $_captured;
  private $_captured_content;
  private $_view;

  /**
   * Set page body to be a renderable object
   *
   * @param \Cubex\View\Renderable $view
   *
   * @return WebPage
   */
  public function setView(Renderable $view)
  {
    $this->_view = $view;

    return $this;
  }

  /**
   * Get Controller shortcut
   *
   * @return Controller
   */
  public function controller()
  {
    return Cubex::core()->controller();
  }

  /**
   * Set HTTP Status Code
   *
   * @param int $status
   *
   * @return WebPage
   */
  public function setHttpStatus($status = 200)
  {
    $this->_http_status = $status;

    return $this;
  }

  /**
   * HTTP Status Code
   *
   * @return int
   */
  public function getHttpStatus()
  {
    return $this->_http_status;
  }

  /**
   * Page Character Set
   *
   * @return string
   */
  public function getCharset()
  {
    return 'UTF-8';
  }

  public function getTitle()
  {
    return $this->_title;
  }

  /**
   * Set Webpage Title
   *
   * @param $title
   *
   * @return WebPage
   */
  public function setTitle($title)
  {
    $this->_title = $title;

    return $this;
  }

  /**
   * Get defined meta tags
   *
   * @param null $key
   *
   * @return mixed
   */
  public function getMeta($key = null)
  {
    if($key === null)
    {
      return $this->_meta;
    }
    else return $this->_meta[$key];
  }

  /**
   * Build MetaTags
   *
   * @return string
   */
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

  /**
   * Get CSS
   *
   * @return string
   */
  public function getHead()
  {
    $css_headers = new Partial('<link type="text/css" rel="stylesheet" href="%s" />');
    $css_uris    = Prop::getResourceUris('css');
    if($css_uris)
    {
      $css_headers->addElements($css_uris);
    }
    return $css_headers . $this->getMetaHTML();
  }

  /**
   * Render body content or captured content
   *
   * @return mixed
   */
  public function getBody()
  {
    $view = $this->_view;
    if($view instanceof Renderable)
    {
      $result = $view->render();
    }
    else
    {
      $result = $this->capturedContent();
    }

    return $this->minifyHtml($result);
  }

  /**
   * Include JavaScript
   *
   * @return \Cubex\View\Partial
   */
  public function getClosing()
  {
    $js_items = new Partial('<script type="text/javascript" src' . '="%s"></script>');
    $js_uris  = Prop::getResourceUris('js');
    if($js_uris)
    {
      $js_items->addElements($js_uris);
    }
    return $js_items;
  }

  /**
   * Build HTML upto opening Body tag
   *
   * @return string
   */
  public function renderHead()
  {
    $this->preRender();
    $charset = $this->getCharset();
    $title   = $this->getTitle();
    $head    = $this->getHead();

    $method      = \strtoupper(isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : 'GET');
    $request_url = Cubex::request()->getPath();
    $request_url .= '?' . \http_build_query(Cubex::request()->variables(), '', '&amp;');

    $noscript = '<meta http-equiv="refresh" content="0; URL=' . $request_url . '&amp;__noscript__=1" />';
    if(Cubex::request()->jsSupport() === false) $noscript = '';

    $response = "<!DOCTYPE html>\n"
    . '<html class="no_js"><head><meta charset="' . $charset . '" />'
    . '<script>function envPop(a){function b(c) {for (var d in a)c[d] = a[d];};'
    . 'window.Env = Env = window.Env || {};b(Env);};'
    . "!function(d){d.className=d.className.replace('no_js', '');}(document.documentElement);"
    . 'envPop({"method":"' . $method . '"});</script><noscript>' . $noscript . '</noscript>'
    . '<title>' . $title . '</title>' . $head . '</head><body>';

    return $response;
  }

  /**
   * get Body content
   *
   * @return mixed
   */
  public function renderBody()
  {
    return $this->getBody();
  }

  /**
   * Closing Body and HTML Tags
   *
   * @return string
   */
  public function renderClosing()
  {
    return $this->getClosing() . '</body></html>';
  }

  /**
   * Render whole webpage
   *
   * @return string
   */
  public function render()
  {
    $this->preRender();
    return $this->renderHead() . $this->renderBody() . $this->renderClosing();
  }


  final public function beginCapture()
  {
    $this->_captured = false;
    \ob_start();
  }

  final public function capturedContent()
  {
    return $this->_captured_content;
  }

  final public function endCapture()
  {
    if($this->_captured === false)
    {
      $this->_captured_content = \ob_get_clean();
      $this->_captured         = true;
    }
    else $this->_captured = false;
  }

  /**
   * @return \Cubex\View\Render
   */
  public function capturedData()
  {
    $this->endCapture();
    return new Render($this->capturedContent());
  }

  final public function preRender()
  {
    if($this->_captured === false) $this->endCapture();
  }

  /**
   * Minify HTML code
   *
   * @param $html
   *
   * @return mixed
   */
  public function minifyHtml($html)
  {
    if(!Cubex::config("response")->getBool("minify_html", true))
    {
      return $html;
    }
    $html = preg_replace('/<!--[^\[](.|\s)*?-->/', '', $html); //Strip HTML Comments

    $search  = array(
      '/\>[^\S ]+/s', //strip whitespaces after tags, except space
      '/[^\S ]+\</s', //strip whitespaces before tags, except space
      '/(\s)+/s' // shorten multiple whitespace sequences
    );
    $replace = array(
      '>',
      '<',
      '\\1'
    );
    return \preg_replace($search, $replace, $html);
  }
}
