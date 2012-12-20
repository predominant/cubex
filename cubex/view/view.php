<?php
/**
 * User: brooke.bryan
 * Date: 09/12/12
 * Time: 17:03
 * Description: Aspects are PHP compiled views as alternatives to phtml files
 */

namespace Cubex\View;

use Cubex\Project\Application;
use Cubex\Dispatch\Dispatcher;
use Cubex\Cubex;
use Cubex\Controller\WebpageController;

/**
 * View
 */
abstract class View extends Dispatcher implements Renderable
{
  abstract public function render();

  /**
   * @return string
   */
  public function __toString()
  {
    try
    {
      return $this->render();
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
  }

  /**
   * Attempt to set page title
   *
   * @param string $title
   *
   * @return View
   */
  public function setTitle($title = '')
  {
    $controller = Cubex::controller();
    if($controller instanceof WebpageController)
    {
      $controller->setTitle($title);
    }
    return $this;
  }

  /**
   * Translate string to locale
   *
   * @param $message string $string
   *
   * @return string
   */
  public function t($message)
  {
    return Application::$app->t($message);
  }

  /**
   * Translate plural
   *
   * @param      $singular
   * @param null $plural
   * @param int  $number
   *
   * @return string
   */
  public function p($singular, $plural = null, $number = 0)
  {
    return Application::$app->p($singular, $plural, $number);
  }

  /**
   *
   * Translate plural, converting (s) to '' or 's'
   *
   */
  public function tp($text, $number)
  {
    return Application::$app->tp($text, $number);
  }
}
