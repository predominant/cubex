<?php
/**
 * User: brooke.bryan
 * Date: 09/12/12
 * Time: 18:33
 * Description:
 */

namespace Cubex\Language;

interface Loader
{

  /**
   * Translate String
   *
   * @param $textdomain
   * @param $message
   * @return string
   */
  public function t($textdomain, $message);

  /**
   *
   * Translate plural, converting (s) to '' or 's'
   *
   * @param      $textdomain
   * @param      $text
   * @param int  $number
   * @return string
   */
  public function tp($textdomain, $text, $number);

  /**
   * Translate plural
   *
   * @param      $textdomain
   * @param      $singular
   * @param null $plural
   * @param int  $number
   * @return string
   */
  public function p($textdomain, $singular, $plural = null, $number = 0);

  public function bindLanguage($textdomain, $filepath);
}
