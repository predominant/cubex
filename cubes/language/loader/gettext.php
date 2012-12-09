<?php
/**
 * User: brooke.bryan
 * Date: 09/12/12
 * Time: 18:25
 * Description:
 */

namespace Cubex\Language\Loader;

class Gettext
{

  protected $_textdomain = 'messages';
  private $_bound_td = false;

  /**
   * Translate String
   *
   * @param $textdomain
   * @param $message
   * @return string
   */
  public function t($textdomain, $message)
  {
    if(!\function_exists('\dgettext')) return (string)$message;

    return \dgettext($textdomain, $message);
  }

  /**
   *
   * Translate plural, converting (s) to '' or 's'
   *
   */
  public function tp($textdomain, $text, $number)
  {
    return $this->p(
      $textdomain,
      str_replace('(s)', '', $text),
      str_replace('(s)', 's', $text),
      $number
    );
  }

  /**
   * Translate plural
   *
   * @param      $textdomain
   * @param      $singular
   * @param null $plural
   * @param int  $number
   * @return string
   */
  public function p($textdomain, $singular, $plural = null, $number = 0)
  {
    if(!\function_exists('\dngettext'))
    {
      $translated = $number == 1 ? $singular : $plural;
    }
    else
    {
      $translated = \dngettext($textdomain, $singular, $plural, $number);
    }

    if(\substr_count($translated, '%d') == 1)
    {
      $translated = \sprintf($translated, $number);
    }

    return $translated;
  }

  public function bindLanguage($textdomain, $filepath)
  {
    $this->_bound_td = true;
    if(!\function_exists('bindtextdomain')) return false;

    return \bindtextdomain($textdomain, $filepath . '\\locale');
  }
}
