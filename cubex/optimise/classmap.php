<?php
/**
 * User: Brooke
 * Date: 01/01/13
 * Time: 20:24
 * Description:
 */

namespace Cubex\Optimise;

use Cubex\Cubex;
use Cubex\Data\Handler;

class ClassMap
{
  public function __construct($args = null)
  {
    $params = new Handler($args);
    $path   = $params->getStr("path", dirname(__DIR__));
    echo "Processing: " . $path;
    $this->saveClassMap($path, $this->createMap($this->compileDirectory($path)));
  }

  public function compileDirectory($directory, $path = '')
  {
    $map = array();
    if($handle = \opendir($directory . $path))
    {
      while(false !== ($entry = \readdir($handle)))
      {
        if(\in_array($entry, array('.', '..', 'locale')))
        {
          continue;
        }

        if(\is_dir($directory . $path . DIRECTORY_SEPARATOR . $entry))
        {
          $map = $map + (array)$this->compileDirectory($directory, $path . DIRECTORY_SEPARATOR . $entry);
        }
        else
        {
          if(substr($entry, -3) == 'php')
          {
            $tokens     = token_get_all(file_get_contents($directory . $path . DIRECTORY_SEPARATOR . $entry));
            $classToken = $namespaceToken = false;
            $namespace  = '';
            foreach($tokens as $token)
            {
              if(is_array($token))
              {
                if($token[0] == T_NAMESPACE)
                {
                  $namespace      = '';
                  $namespaceToken = true;
                }
                else if($namespaceToken && (in_array($token[0], [T_STRING, T_NS_SEPARATOR])))
                {
                  $namespace .= $token[1];
                }
                else if($namespaceToken && !in_array($token[0], [T_WHITESPACE]))
                {
                  $namespaceToken = false;
                }

                if(in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT]))
                {
                  $classToken = true;
                }
                else if($classToken && $token[0] == T_STRING)
                {
                  $map[$namespace . '\\' . $token[1]] = ltrim(
                    $path . DIRECTORY_SEPARATOR . $entry, DIRECTORY_SEPARATOR
                  );
                  $classToken                         = false;
                }
              }
            }
          }
        }
      }

      \closedir($handle);
    }
    return $map;
  }

  public function saveClassMap($directory, $map)
  {
    return file_put_contents($directory . DIRECTORY_SEPARATOR . 'classmap.ini', $map);
  }

  public function createMap(array $classmap)
  {
    $map = '';
    foreach($classmap as $class => $location)
    {
      $map .= "$class = $location\n";
    }
    return $map;
  }
}
