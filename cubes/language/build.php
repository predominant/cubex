<?php
/**
 * User: brooke.bryan
 * Date: 01/12/12
 * Time: 16:05
 * Description:
 */

namespace Cubex\Language;

/**
 * Build
 */
class Build
{

  /**
   * @var array|null
   */
  private $_analyse = array('applications', 'modules', 'widgets');

  /**
   * @var
   */
  private $_projectDir;

  /**
   * @var string
   */
  private $_msgFmt = "msgfmt -V";

  /**
   * @var array
   */
  private $_languages = array();

  /**
   * @param      $projectDir
   * @param null $analyse
   */
  public function __construct($projectDir, $analyse = null)
  {
    if($analyse !== null)
      $this->_analyse = $analyse;

    $this->_projectDir = $projectDir;
  }

  /**
   * @param string $path
   *
   * @return Build
   */
  public function msgFmtPath($path = "msgfmt -V")
  {
    $this->_msgFmt = $path;

    return $this;
  }

  /**
   * @param Translator $translator
   */
  public function compile(Translator $translator)
  {
    foreach($this->_analyse as $type)
    {
      $runDir = $this->_projectDir . DIRECTORY_SEPARATOR . $type;
      if($handle = \opendir($runDir))
      {
        while(false !== ($entry = \readdir($handle)))
        {
          if(\in_array($entry, array('.', '..', 'locale')))
            continue;

          if(\is_dir($runDir . DIRECTORY_SEPARATOR . $entry))
          {
            $mfile   = \md5($type . DIRECTORY_SEPARATOR . $entry);
            $analyse = new Analyse();
            $analyse->processDirectory($runDir . DIRECTORY_SEPARATOR . $entry, '');
            $localeDir = $runDir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'locale';
            if(!\file_exists($localeDir))
            {
              \mkdir($localeDir);
            }
            \file_put_contents(
              $localeDir . DIRECTORY_SEPARATOR . 'messages.po', $analyse->generatePO('', new Notranslator())
            );

            foreach($this->_languages as $language)
            {
              $languageDir = $localeDir . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'LC_MESSAGES';

              if(!\file_exists($languageDir))
              {
                \mkdir($languageDir, 0777, true);
              }

              \file_put_contents(
                $languageDir . DIRECTORY_SEPARATOR . $mfile . '.po',
                $analyse->generatePO($language, $translator)
              );

              $tfile = $languageDir . DIRECTORY_SEPARATOR . $mfile;
              \shell_exec($this->_msgFmt . ' -o "' . $tfile . '.mo" "' . $tfile . '.po"');
            }
          }
        }
        \closedir($handle);
      }
    }
  }

  /**
   * @param $language
   *
   * @return Build
   */
  public function addLanguage($language)
  {
    $this->_languages[] = $language;

    return $this;
  }
}
