<?php
/**
 * User: brooke.bryan
 * Date: 28/11/12
 * Time: 17:35
 * Description:
 */
namespace Cubex\Language;

class Analyse
{

  protected $_translations = array();

  public function processDirectory($base, $directory)
  {
    if($handle = opendir($base . $directory))
    {
      while(false !== ($entry = readdir($handle)))
      {
        if(in_array($entry, array('.', '..', 'locale'))) continue;

        if(is_dir($base . $directory . DIRECTORY_SEPARATOR . $entry))
        {
          $this->processDirectory($base, $directory . DIRECTORY_SEPARATOR . $entry);
        }
        else if(substr($entry, -4) == '.php' || substr($entry, -6) == '.phtml')
        {
          $this->processFile($base, $directory . DIRECTORY_SEPARATOR . $entry);
        }
      }

      closedir($handle);
    }
  }

  public function processFile($base, $path)
  {
    $content   = file_get_contents($base . $path);
    $path      = ltrim($path, DIRECTORY_SEPARATOR);
    $tokens    = token_get_all($content);
    $startline = 0;
    $msgid     = '';

    foreach($tokens as $token)
    {
      if($token[0] == 307 && $token[1] == 't')
      {
        $startline = $token[2];
        $started   = true;
      }

      if($started && is_scalar($token) && $token == ')')
      {
        $this->_translations[$msgid][] = array($path, $startline);
        $msgid                         = '';
        $started                       = false;
      }

      if($started && $token[0] == 315)
      {
        $msgid .= substr($token[1], 1, -1);
      }
    }
  }

  public function generatePO()
  {
    $result = '';

    $result .= '
msgid ""
msgstr ""

"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"

';

    foreach($this->_translations as $message => $appearances)
    {
      $result .= '#:';
      foreach($appearances as $appearance)
      {
        $result .= ' ' . implode(':', $appearance);
      }
      $result .= "\n";
      if(strlen($message) < 80)
      {
        $result .= 'msgid "' . $message . '"';
      }
      else
      {
        $result .= 'msgid ""' . "\n";
        $result .= '"' . $this->iconv_wordwrap($message, 76, " \"\n\"") . '"';
      }
      $result .= "\n";
      $result .= 'msgstr ""';
      $result .= "\n\n";
    }

    return $result;
  }

  function iconv_wordwrap($string, $width = 75, $break = "\n", $cut = false, $charset = 'utf-8')
  {
    $stringWidth = \iconv_strlen($string, $charset);
    $breakWidth  = \iconv_strlen($break, $charset);

    if(strlen($string) === 0)
    {
      return '';
    }
    elseif($breakWidth === null)
    {
      throw new \Exception('Break string cannot be empty');
    }
    elseif($width === 0 && $cut)
    {
      throw new \Exception('Can\'t force cut when width is zero');
    }

    $result    = '';
    $lastStart = $lastSpace = 0;

    for($current = 0; $current < $stringWidth; $current++)
    {
      $char = iconv_substr($string, $current, 1, $charset);

      if($breakWidth === 1)
      {
        $possibleBreak = $char;
      }
      else
      {
        $possibleBreak = iconv_substr($string, $current, $breakWidth, $charset);
      }

      if($possibleBreak === $break)
      {
        $result .= iconv_substr($string, $lastStart, $current - $lastStart + $breakWidth, $charset);
        $current += $breakWidth - 1;
        $lastStart = $lastSpace = $current + 1;
      }
      elseif($char === ' ')
      {
        if($current - $lastStart >= $width)
        {
          $result .= iconv_substr($string, $lastStart, $current - $lastStart, $charset) . $break;
          $lastStart = $current + 1;
        }

        $lastSpace = $current;
      }
      elseif($current - $lastStart >= $width && $cut && $lastStart >= $lastSpace)
      {
        $result .= iconv_substr($string, $lastStart, $current - $lastStart, $charset) . $break;
        $lastStart = $lastSpace = $current;
      }
      elseif($current - $lastStart >= $width && $lastStart < $lastSpace)
      {
        $result .= iconv_substr($string, $lastStart, $lastSpace - $lastStart, $charset) . $break;
        $lastStart = $lastSpace = $lastSpace + 1;
      }
    }

    if($lastStart !== $current)
    {
      $result .= iconv_substr($string, $lastStart, $current - $lastStart, $charset);
    }

    return $result;
  }

}

