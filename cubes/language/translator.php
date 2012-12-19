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
   * @param string $text            Text to translate
   * @param string $sourceLanguage original text language
   * @param string $targetLanguage expected return language
   * @return string Translation
   */
  public function translate($text, $sourceLanguage, $targetLanguage);
}
