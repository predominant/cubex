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

  protected $_translations = array('single' => array(), 'plural' => array());

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
    $startline = $building = 0;
    $msgid     = $type = $msgid_plural = '';
    $started   = false;

    foreach($tokens as $token)
    {
      if($token[0] == 307 && $token[1] == 't')
      {
        $building  = 0;
        $msgid     = $msgid_plural = '';
        $type      = 'single';
        $startline = $token[2];
        $started   = true;
      }

      if($token[0] == 307 && $token[1] == 'p')
      {
        $msgid     = $msgid_plural = '';
        $type      = 'plural';
        $building  = 0;
        $startline = $token[2];
        $started   = true;
      }

      if($token == ',' && $type == 'plural')
      {
        $building = 1;
      }

      if($started && is_scalar($token) && $token == ')')
      {
        if($type == 'plural')
        {
          $this->_translations[$type][md5($msgid . $msgid_plural)]['data']    = array($msgid, $msgid_plural);
          $this->_translations[$type][md5($msgid . $msgid_plural)]['options'] = array($path, $startline);
        }
        else
        {
          $this->_translations[$type][$msgid][] = array($path, $startline);
        }

        $started = false;
      }

      if($started && $token[0] == 315)
      {
        if($building == 0)
        {
          $msgid .= substr($token[1], 1, -1);
        }
        else
        {
          $msgid_plural .= substr($token[1], 1, -1);
        }
      }
    }
  }

  public function generatePO($language, Translator $translator, $source_language = 'en')
  {
    $result = '';

    foreach($this->_translations as $build_type => $translations)
    {
      foreach($translations as $message => $appearances)
      {
        if($build_type == 'plural')
        {
          $data        = $appearances;
          $appearances = array($data['options']);
          $message     = $data['data'];
        }

        $result .= '#:';
        foreach($appearances as $appearance)
        {
          $result .= ' ' . implode(':', $appearance);
        }

        $result .= "\n";
        if($build_type == 'single')
        {
          $translated = $translator->translate($message, $source_language, $language);
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
          if(strlen($translated) < 80)
          {
            $result .= 'msgstr "' . $translated . '"';
          }
          else
          {
            $result .= 'msgstr ""' . "\n";
            $result .= '"' . $this->iconv_wordwrap($translated, 76, " \"\n\"") . '"';
          }
          $result .= "\n\n";
        }
        else if($build_type == 'plural')
        {
          $singular = $translator->translate($message[0], $source_language, $language);
          $plural = $translator->translate($message[1], $source_language, $language);

          if(strlen($message[0]) < 80)
          {
            $result .= 'msgid "' . $message[0] . '"';
          }
          else
          {
            $result .= 'msgid ""' . "\n";
            $result .= '"' . $this->iconv_wordwrap($message[0], 76, " \"\n\"") . '"';
          }

          $result .= "\n";

          if(strlen($message[1]) < 80)
          {
            $result .= 'msgid_plural "' . $message[1] . '"';
          }
          else
          {
            $result .= 'msgid_plural ""' . "\n";
            $result .= '"' . $this->iconv_wordwrap($message[1], 76, " \"\n\"") . '"';
          }

          $result .= "\n";
          $result .= 'msgstr[0] "'. $singular .'"';
          $result .= "\n";
          $result .= 'msgstr[1] "'. $plural .'"';
          $result .= "\n\n";
        }
      }
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

