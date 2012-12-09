<?php
/**
 * User: brooke.bryan
 * Date: 09/12/12
 * Time: 17:03
 * Description: Aspects are PHP compiled views as alternatives to phtml files
 */

namespace Cubex\View;

abstract class Aspect implements Renderable
{

  abstract public function render();

  public function __tostring()
  {
    return $this->render();
  }

  /**
   * Translate string to locale
   *
   * @param $message string $string
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
