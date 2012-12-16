<?php
/**
 * User: Brooke
 * Date: 15/12/12
 * Time: 22:45
 * Description:
 */
namespace Cubex\Dispatch;

final class Prop
{
  private static $requires = array("css" => array(), "js" => array(), "packages" => array());

  public static function requireResource(Dispatcher $source, $resource, $type = 'css')
  {
    if(\in_array($type, array('css', 'js')))
    {
      if(substr($resource, 0, 7) == 'http://'
      || substr($resource, 0, 8) == 'https://'
      || substr($resource, 0, 3) == '://'
      )
      {
        $uri   = $resource;
        $group = 'fullpath';
      }
      else
      {
        $uri = $source->getDispatchFabricator()->resource($resource);
        if(substr($resource, 0, 1) == '/')
        {
          $group = 'esabot';
        }
        else
        {
          $group = $source->getDispatchFabricator()->getEntityHash();
        }
      }

      self::$requires[$type][] = array(
        'group'    => $group,
        'resource' => $resource,
        'uri'      => $uri
      );
    }
    else
    {
      throw new \Exception("You cannot require a resource of type " . $type);
    }
  }

  public static function requirePackage(Dispatcher $source, $name, $type = 'css')
  {
    if(\in_array($type, array('css', 'js')))
    {
      self::$requires[$type][] = array(
        'group'    => $source->getDispatchFabricator()->getEntityHash(),
        'resource' => 'package',
        'uri'      => $source->getDispatchFabricator()->package($name, $type)
      );

      self::$requires["packages"][$type . '_' . $source->getDispatchFabricator()->getEntityHash()] = true;
    }
    else
    {
      throw new \Exception("You cannot require a resource of type " . $type);
    }
  }

  public static function getResourceUris($type = 'css')
  {
    $out = array();
    foreach(self::$requires[$type] as $res)
    {
      if(!isset(self::$requires["packages"][$type . '_' . $res['group']]) || $res['resource'] == 'package')
      {
        $out[] = $res['uri'];
      }
    }
    return $out;
  }
}
