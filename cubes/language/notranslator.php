<?php
/**
 * User: brooke.bryan
 * Date: 03/12/12
 * Time: 17:20
 * Description:
 */
namespace Cubex\Language;

class Notranslator implements Translator
{

  /**
   * @param string $text            Text to translate
   * @param string $source_language original text language
   * @param string $target_language expected return language
   * @return string Translation
   */
  public function translate($text, $source_language, $target_language)
  {
    return $text;
  }
}
