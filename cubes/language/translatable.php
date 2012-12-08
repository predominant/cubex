<?php
/**
 * User: brooke.bryan
 * Date: 25/11/12
 * Time: 12:52
 * Description:
 */

namespace Cubex\Language;

class Translatable
{

  protected $_textdomain = 'messages';
  private $_bound_td = false;

  /**
   * Translate string to locale, wrapper for gettext
   *
   * @link http://php.net/manual/en/function.gettext.php
   * @param $message string $string
   * @return string
   */
  public function t($message)
  {
    return dgettext($this->textDomain(), $message);
  }

  /**
   * Translate plural, using specific domain
   *
   * @link http://php.net/manual/en/function.dngettext.php
   * @param      $singular
   * @param null $plural
   * @param int  $number
   * @return string
   */
  public function p($singular, $plural = null, $number = 0)
  {
    return dngettext($this->textDomain(), $singular, $plural, $number);
  }

  public function textDomain()
  {
    $path              = str_replace(dirname(dirname($this->filePath())) . DIRECTORY_SEPARATOR, '', $this->filePath());
    $this->_textdomain = md5($path);

    if(!$this->_bound_td) $this->bindLanguage();

    return $this->_textdomain;
  }

  public function bindLanguage()
  {
    $this->_bound_td = true;

    return bindtextdomain($this->textDomain(), $this->filePath() . '\\locale');
  }

  public function filePath()
  {
    $reflector = new \ReflectionClass(get_class($this));

    return dirname($reflector->getFileName());
  }
}
