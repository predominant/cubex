<?php
/**
 * User: brooke.bryan
 * Date: 25/11/12
 * Time: 12:52
 * Description:
 */

namespace Cubex\Language;

use Cubex\Language\Loader\Gettext;
use Cubex\Dispatch\Dispatcher;

/**
 * Create a translatable class
 */
abstract class Translatable extends Dispatcher
{

  protected $_textdomain = 'messages';
  private $_boundTd = false;
  /**
   * @var Loader
   */
  private $_translator;

  /**
   * @return Loader
   */
  public function getTranslator()
  {
    $this->_translator = new Gettext();

    return $this->_translator;
  }

  /**
   * Translate string
   *
   * @param $message string $string
   *
   * @return string
   */
  public function t($message)
  {
    return $this->getTranslator()->t($this->textDomain(), $message);
  }

  /**
   *
   * Translate plural, converting (s) to '' or 's'
   *
   */
  public function tp($text, $number)
  {
    return $this->p(
      \str_replace('(s)', '', $text),
      \str_replace('(s)', 's', $text),
      $number
    );
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
    $translated = $this->getTranslator()->p($this->textDomain(), $singular, $plural, $number);

    if(\substr_count($translated, '%d') == 1)
    {
      $translated = \sprintf($translated, $number);
    }

    return $translated;
  }

  public function textDomain()
  {
    $path = \str_replace(\dirname(\dirname($this->filePath())) . DIRECTORY_SEPARATOR, '', $this->filePath());

    $this->_textdomain = \md5($path);

    if(!$this->_boundTd) $this->bindLanguage();

    return $this->_textdomain;
  }

  public function bindLanguage()
  {
    $this->_boundTd = true;

    return $this->getTranslator()->bindLanguage($this->textDomain(), $this->filePath() . '\\locale');
  }

  /**
   * File path for the current class
   *
   * @return string
   */
  public function filePath()
  {
    $reflector = new \ReflectionClass(\get_class($this));

    return \dirname($reflector->getFileName());
  }
}
