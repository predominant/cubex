<?php
/**
 * User: brooke.bryan
 * Date: 03/12/12
 * Time: 17:20
 * Description:
 */
namespace Cubex\Language;

use Cubex\Cubex;

class GoogleTranslator implements Translator
{

  /**
   * @param string $text            Text to translate
   * @param string $source_language original text language
   * @param string $target_language expected return language
   * @return string Translation
   */
  public function translate($text, $source_language, $target_language)
  {
    $translation = $text;

    /* sprintf Replacements */
    $pattern = '/(?:%%|%(?:[0-9]+\$)?[+-]?(?:[ 0]|\'.)?-?[0-9]*(?:\.[0-9]+)?[bcdeufFosxX])/';
    $replace = '<span class="notranslate">$0</span>';
    $text    = \preg_replace($pattern, $replace, $text);

    $pattern = '/&[^\s]*;/';
    $replace = '<span class="notranslate">$0</span>';
    $text    = \preg_replace($pattern, $replace, $text);

    $data = array(
      'key'    => Cubex::config('locale')->getStr('google_api'),
      'source' => \substr($source_language, 0, 2),
      'target' => \substr($target_language, 0, 2),
      'q'      => $text
    );

    $service = \curl_init();
    \curl_setopt($service, CURLOPT_URL, 'https://www.googleapis.com/language/translate/v2?' . \http_build_query($data));
    \curl_setopt($service, CURLOPT_RETURNTRANSFER, true);
    \curl_setopt($service, CURLOPT_SSL_VERIFYHOST, false);
    \curl_setopt($service, CURLOPT_SSL_VERIFYPEER, false);
    $response = \curl_exec($service);
    \curl_close($service);

    $pattern  = '/<span class="notranslate">([^<]*)<\/span>/';
    $response = \preg_replace($pattern, '$1', \urldecode($response));

    $response = \json_decode($response);
    if(isset($response->data->translations[0]->translatedText))
    {
      $translation = $response->data->translations[0]->translatedText;
    }

    $pattern     = '/<span class="notranslate">([^<]*)<\/span>/';
    $translation = \preg_replace($pattern, '$1', $translation);

    return $translation;
  }
}
