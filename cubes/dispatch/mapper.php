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
class Mapper
{
  /**
   * Generate array of directory structure
   *
   * @param        $directory
   * @param string $sub_directory
   *
   * @return array
   */
  public static function mapDirectory($directory, $sub_directory = '')
  {
    $map = array();

    try
    {
      if($handle = \opendir($directory . $sub_directory))
      {
        while(false !== ($filename = \readdir($handle)))
        {
          if(\in_array($filename, array('.', '..'))) continue;

          if(\is_dir($directory . $sub_directory . DIRECTORY_SEPARATOR . $filename))
          {
            $map = \array_merge($map, self::mapDirectory($directory, $sub_directory . DIRECTORY_SEPARATOR . $filename));
          }
          else
          {
            $rel            = $sub_directory . DIRECTORY_SEPARATOR . $filename;
            $safe_rel       = \ltrim(\str_replace('\\', '/', $rel), '/');
            $map[$safe_rel] = \md5_file($directory . $rel);
          }
        }

        \closedir($handle);
      }
    }
    catch(\Exception $e)
    {
      //Unable to open directory (probably)
    }

    return $map;
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
      \file_put_contents($path . DIRECTORY_SEPARATOR . $filename, $mapped);
      return true;
    }
    catch(\Exception $e)
    {
      //Unable to write file
      return false;
    }
  }
}
