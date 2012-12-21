<?php
/**
 * User: Brooke
 * Date: 15/12/12
 * Time: 22:45
 * Description:
 */
namespace Cubex\Dispatch;

/**
 * Combine all dispatch resources
 */
final class Prop
{
  private static $requires = array("css" => array(), "js" => array(), "packages" => array());

  /**
   * Mark a CSS or JS script as being required, which can be picked up on render
   *
   * @param Dispatcher $source
   * @param            $resource
   * @param string     $type
   *
   * @throws \Exception
   */
  public static function requireResource(Dispatcher $source, $resource, $type = 'css')
  {
    if(\in_array($type, array('css', 'js')))
    {
      if(
        \substr($resource, 0, 7) == 'http://'
        || \substr($resource, 0, 8) == 'https://'
        || \substr($resource, 0, 2) == '//'
      )
      {
        $uri   = $resource;
        $group = 'fullpath';
      }
      else
      {
        $uri = $source->getDispatchFabricator()->resource($resource);
        if(\substr($resource, 0, 1) == '/')
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

  /**
   * Mark a complete package as being required for a response, this will include all $type files for an entity group
   *
   * @param Dispatcher $source
   * @param            $name
   * @param string     $type
   *
   * @throws \Exception
   */
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

  /**
   * Return an array of URIs that need to be rendered to the user
   *
   * @param string $type
   *
   * @return array
   */
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
