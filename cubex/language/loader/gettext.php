<?php
/**
 * User: brooke.bryan
 * Date: 09/12/12
 * Time: 18:25
 * Description:
 */

namespace Cubex\Language\Loader;

/**
 * Gettext
 */
class Gettext
{

  /**
   * @var string
   */
  protected $_textdomain = 'messages';
  /**
   * @var bool
   */
  private $_boundTd = false;

  /**
   * Translate String
   *
   * @param $textDomain
   * @param $message
   *
   * @return string
   */
  public function t($textDomain, $message)
  {
    if(!\function_exists('\dgettext'))
      return (string)$message;

    return \dgettext($textDomain, $message);
  }

  /**
   * Translate plural, converting (s) to '' or 's'

   */
  public function tp($textDomain, $text, $number)
  {
    return $this->p(
      $textDomain,
      str_replace('(s)', '', $text),
      str_replace('(s)', 's', $text),
      $number
    );
  }

  /**
   * Translate plural
   *
   * @param      $textDomain
   * @param      $singular
   * @param null $plural
   * @param int  $number
   *
   * @return string
   */
  public function p($textDomain, $singular, $plural = null, $number = 0)
  {
    if(!\function_exists('\dngettext'))
    {
      $translated = $number == 1 ? $singular : $plural;
    }
    else
    {
      $translated = \dngettext($textDomain, $singular, $plural, $number);
    }

    if(\substr_count($translated, '%d') == 1)
    {
      $translated = \sprintf($translated, $number);
    }

    return $translated;
  }

  /**
   * @param $textDomain
   * @param $filePath
   *
   * @return bool|string
   */
  public function bindLanguage($textDomain, $filePath)
  {
    $this->_boundTd = true;
    if(!\function_exists('bindtextdomain'))
      return false;

    return \bindtextdomain($textDomain, $filePath . '\\locale');
  }
}
