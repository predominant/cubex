<?php
/**
 * User: brooke.bryan
 * Date: 03/12/12
 * Time: 16:58
 * Description:
 */

namespace Cubex\Language;

/**
 *
 */
interface Translator
{

  /**
   * @param string $text Text to translate
   * @param string $source_language original text language
   * @param string $target_language expected return language
   * @return string Translation
   */
  public function translate($text,$source_language,$target_language);
}
