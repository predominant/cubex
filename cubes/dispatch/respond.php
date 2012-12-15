<?php
/**
 * User: brooke.bryan
 * Date: 15/12/12
 * Time: 11:39
 * Description:
 */

namespace Cubex\Dispatch;

/**
 * Respond to dispatch requests
 */
class Respond
{
  /**
   * Render output
   *
   * @param $path
   */
  protected function render($path)
  {

  }

  /**
   * Supported file types that can be processed using dispatch
   *
   * @return array
   */
  protected function supportedTypes()
  {
    return array(
      'css' => 'text/css; charset=utf-8',
      'js'  => 'text/javascript; charset=utf-8',
      'png' => 'image/png',
      'jpg' => 'image/jpg',
      'gif' => 'image/gif',
      'swf' => 'application/x-shockwave-flash',
    );
  }
}
