<?php
/**
 * User: brooke.bryan
 * Date: 01/12/12
 * Time: 16:05
 * Description:
 */

namespace Cubex\Language;

class Build
{

  private $_analyse = array('application', 'module', 'widgets');
  private $_project_dir;
  private $_languages = array();

  public function __construct($project_dir, $analyse = array('application', 'module', 'widgets'))
  {
    $this->_analyse     = $analyse;
    $this->_project_dir = $project_dir;
  }

  public function compile(Translator $translator)
  {
    $converter = new Mo();
    foreach($this->_analyse as $type)
    {
      $run_dir = $this->_project_dir . DIRECTORY_SEPARATOR . $type;
      if($handle = opendir($run_dir))
      {
        while(false !== ($entry = readdir($handle)))
        {
          if(in_array($entry, array('.', '..', 'locale'))) continue;

          if(is_dir($run_dir . DIRECTORY_SEPARATOR . $entry))
          {
            $mfile   = md5($type . DIRECTORY_SEPARATOR . $entry);
            $analyse = new Analyse();
            $analyse->processDirectory($run_dir . DIRECTORY_SEPARATOR . $entry, '');
            $locale_dir = $run_dir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'locale';
            if(!file_exists($locale_dir))
            {
              mkdir($locale_dir);
            }
            file_put_contents(
              $locale_dir . DIRECTORY_SEPARATOR . 'messages.po', $analyse->generatePO('', new Notranslator())
            );

            foreach($this->_languages as $language)
            {
              $language_dir = $locale_dir . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'LC_MESSAGES';

              if(!file_exists($language_dir))
              {
                mkdir($language_dir, 0777, true);
              }

              file_put_contents(
                $language_dir . DIRECTORY_SEPARATOR . $mfile . '.po',
                $analyse->generatePO($language, $translator)
              );

              $converter->phpmo_convert($language_dir . DIRECTORY_SEPARATOR . $mfile . '.po');
            }

          }
        }
        closedir($handle);

      }
    }
  }

  public function addLanguage($language)
  {
    $this->_languages[] = $language;

    return $this;
  }
}
