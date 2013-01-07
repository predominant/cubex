<?php
/**
 * User: brooke.bryan
 * Date: 15/12/12
 * Time: 15:57
 * Description:
 */
namespace Cubex\Dispatch;

/**
 * Generate dispatch maps
 */
use Cubex\Cli\Shell;
use Cubex\Cubex;

class Mapper
{
  private $_ignoredDirectories = array('.', '..');
  private $_ignoredFiles = array('.gitignore', 'dispatch.ini');

  /**
   * When called from the CLI, the CLI process will be kicked off
   */
  public function __construct()
  {
    /**
     * php "bin\cli.php" "Cubex\Dispatch\Mapper" --env=ENVIRONMENT
     **/
    if(CUBEX_CLI)
    {
      $this->_cli();
    }
  }

  private function _cli()
  {
    echo \str_repeat("\n", 100);

    $basePath = Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR;
    $this->cliStartMapper();

    $projectIni  = '';
    $existingMap = Cubex::config("dispatch")->getArr("entity_map");

    echo Shell::colourText("Using Path: ", Shell::COLOUR_FOREGROUND_CYAN) . $basePath . "\n\n";

    foreach(array("", "applications", "components", "modules", "widgets") as $entityGroup)
    {
      echo Shell::colourText("=======================================\n\n", Shell::COLOUR_FOREGROUND_DARK_GREY);
      echo Shell::colourText("Processing ", Shell::COLOUR_FOREGROUND_CYAN);
      echo ($entityGroup == '' ? 'Base' : \ucwords($entityGroup)) . "\n";
      $entityGroup = 'cubex' . (empty($entityGroup) ? '' : '/') . $entityGroup;
      $entities    = $this->_cliFindEntities($basePath, $entityGroup);
      if($entities)
      {
        foreach($entities as $entity)
        {
          echo "\n";
          $entityHash = Fabricate::generateEntityHash($entity);
          if(!isset($existingMap[$entityHash]))
          {
            $projectIni .= "entity_map[" . $entityHash . "] = $entity\n";
          }
          echo Shell::colourText("     Found ", Shell::COLOUR_FOREGROUND_LIGHT_CYAN);
          echo Shell::colourText($entityHash, Shell::COLOUR_FOREGROUND_PURPLE);
          echo " $entity\n";

          echo "           Mapping Directory:   ";
          \flush();
          $mapped = $this->mapDirectory($basePath . $entity);
          echo $this->cliResult($mapped !== false);
          if($mapped)
          {
            echo "           Saving Dispatch Map: ";
            $saved = $this->saveMap($mapped, $basePath . $entity);
            echo $this->cliResult($saved);
          }
        }
      }
      else
      {
        echo Shell::colourText("\n           No Entities Found\n", Shell::COLOUR_FOREGROUND_YELLOW);
      }
      echo "\n";
    }

    $this->cliCompleteMapper($projectIni);
  }

  protected function cliStartMapper()
  {
    $mapper = '_____________                      _____      ______
___  __ \__(_)____________________ __  /_________  /_
__  / / /_  /__  ___/__  __ \  __ `/  __/  ___/_  __ \
_  /_/ /_  / _(__  )__  /_/ / /_/ // /_ / /__ _  / / /
/_____/ /_/  /____/ _  .___/\__,_/ \__/ \___/ /_/ /_/
                    /_/
______  ___
___   |/  /_____ _____________________________
__  /|_/ /_  __ `/__  __ \__  __ \  _ \_  ___/
_  /  / / / /_/ /__  /_/ /_  /_/ /  __/  /
/_/  /_/  \__,_/ _  .___/_  .___/\___//_/
                 /_/     /_/                  ';
    echo Shell::colourText("\n$mapper\n\n", Shell::COLOUR_FOREGROUND_LIGHT_RED);
  }

  protected function cliCompleteMapper($projectIni)
  {
    if(!empty($projectIni))
    {
      echo "\n\n";

      echo Shell::colourText("WARNING: ", Shell::COLOUR_FOREGROUND_RED);
      echo "Your project configuration is incomplete\n\n";
      echo "It is recommended you add the following lines to\n";
      echo "the dispatch section of " . CUBEX_ENV . ".ini\n";

      echo "\n[dispatch]\n";
      echo Shell::colourText($projectIni, Shell::COLOUR_FOREGROUND_LIGHT_BLUE);
    }
    else
    {
      echo Shell::colourText("\n==============================", Shell::COLOUR_FOREGROUND_GREEN);
      echo Shell::colourText("\n|  DISPATCH MAPPER COMPLETE  |", Shell::COLOUR_FOREGROUND_LIGHT_GREEN);
      echo Shell::colourText("\n==============================", Shell::COLOUR_FOREGROUND_GREEN);
    }
    echo "\n";
  }

  /**
   * Output OK / FAILED in CLI Colour
   *
   * @param bool $success
   *
   * @return string
   */
  public function cliResult($success = true)
  {
    if($success)
    {
      $result = " [ ";
      $result .= Shell::colourText("OK", Shell::COLOUR_FOREGROUND_GREEN);
      $result .= " ]\n";
    }
    else
    {
      $result = " [ ";
      $result .= Shell::colourText("FAILED", Shell::COLOUR_FOREGROUND_RED);
      $result .= " ]\n";
    }
    return $result;
  }

  private function _cliFindEntities($path, $group)
  {
    $entities = array();
    try
    {
      if($handle = \opendir($path . $group))
      {
        while(false !== ($filename = \readdir($handle)))
        {
          if(\substr($filename, 0, 1) == '.') continue;

          if($filename != 'src')
          {
            $filename = $filename . DIRECTORY_SEPARATOR . 'src';
          }

          if(\is_dir($path . $group . DIRECTORY_SEPARATOR . $filename))
          {
            $entities[] = $group . '/' . \str_replace('\\', '/', $filename);
          }
        }

        \closedir($handle);
      }
    }
    catch(\Exception $e)
    {
      //Unable to open directory (probably)
    }

    return $entities;
  }

  /**
   * Generate array of directory structure
   *
   * @param        $directory
   * @param string $subDirectory
   *
   * @return array
   */
  public function mapDirectory($directory, $subDirectory = '')
  {
    $map = array();

    try
    {
      if($handle = \opendir($directory . $subDirectory))
      {
        while(false !== ($filename = \readdir($handle)))
        {
          if($this->_shouldMap($filename))
          {
            $rel = $subDirectory . DIRECTORY_SEPARATOR . $filename;
            $file = $directory . $rel;

            if(\is_dir($file))
            {
              $map = \array_merge($map, $this->mapDirectory($directory, $rel));
            }
            else
            {
              $safeRel       = \ltrim(\str_replace('\\', '/', $rel), '/');
              $map[$safeRel] = md5(
                $this->_concatAllRelatedContent(
                  $directory, $subDirectory, $filename
                )
              );
              echo $map[$safeRel] . PHP_EOL;
            }
          }
        }

        \closedir($handle);
      }
    }
    catch(\Exception $e)
    {
      if(CUBEX_CLI) return false;
      //Unable to open directory (probably)
    }
    return $map;
  }

  private function _concatAllRelatedContent($baseDirectory, $subDirectory,
                                            $filename)
  {
    $content = '';
    $directories = $this->_getBrandDirectoryList($baseDirectory);
    $filenames = $this->_getAllPossibleFilenames($filename);

    array_walk(
      $directories,
      function(&$directory, $key, $pathDirectories)
      {
        $directory = $pathDirectories[0] . DIRECTORY_SEPARATOR . $directory;
        $directory .= $pathDirectories[1];
      },
      array($baseDirectory, $subDirectory)
    );

    $directories[] = $baseDirectory . $subDirectory;

    foreach($directories as $directory)
    {
      foreach($filenames as $possibleFilename)
      {
        $currentFileRef = $directory . DIRECTORY_SEPARATOR . $possibleFilename;
        if(file_exists($currentFileRef))
        {
          $content .= file_get_contents($currentFileRef);
        }
      }
    }

    return $content;
  }

  /**
   * @param $directory
   *
   * @return array
   */
  private function _getBrandDirectoryList($directory)
  {
    $directories = array();

    try
    {
      if($handle = \opendir($directory))
      {
        while(false !== ($reference = \readdir($handle)))
        {
          $fullReference = $directory . DIRECTORY_SEPARATOR . $reference;

          if(\is_dir($fullReference) && \substr($reference, 0, 1) === '.')
          {
            if(!in_array($reference, $this->_ignoredDirectories))
            {
              $directories[] = $reference;
            }
          }
        }

        \closedir($handle);
      }
    }
    catch(\Exception $e)
    {
    }

    return $directories;
  }

  /**
   * @param $filename
   *
   * @return array
   */
  private function _getAllPossibleFilenames($filename)
  {
    $filenameParts = explode(".", $filename);
    $filenameExtension = array_pop($filenameParts);
    $filenameName = implode(".", $filenameParts);

    return array(
      "{$filenameName}.pre.{$filenameExtension}",
      "{$filenameName}.{$filenameExtension}",
      "{$filenameName}.post.{$filenameExtension}"
    );
  }

  /**
   * @param $filename
   *
   * @return bool
   */
  private function _shouldMap($filename)
  {
    $shouldMap = true;

    $ignoredFilenameEntries = array_merge(
      $this->_ignoredDirectories, $this->_ignoredFiles
    );
    if(in_array($filename, $ignoredFilenameEntries))
    {
      $shouldMap = false;
    }

    if(\substr($filename, 0, 1) == '.')
    {
      $shouldMap = false;
    }

    return $shouldMap;
  }

  /**
   * Write map to dispatch file (saved in path directory)
   *
   * @param        $map
   * @param        $path
   * @param string $filename
   *
   * @return int
   */
  public static function saveMap($map, $path, $filename = 'dispatch.ini')
  {
    $mapped = '';
    if(!\is_array($map))
    {
      return false;
    }
    foreach($map as $file => $checksum)
    {
      $mapped .= "$file = \"$checksum\"\n";
    }
    try
    {
      $currentMd5 = '';
      /** Do not overwrite the same file - causes havock with rsync */
      if(\file_exists($path . DIRECTORY_SEPARATOR . $filename))
      {
        $currentMd5 = \md5_file($path . DIRECTORY_SEPARATOR . $filename);
      }

      if($currentMd5 != \md5($mapped))
      {
        \file_put_contents($path . DIRECTORY_SEPARATOR . $filename, $mapped);
      }
      return true;
    }
    catch(\Exception $e)
    {
      //Unable to write file
      return false;
    }
  }
}
