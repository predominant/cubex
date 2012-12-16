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
  private static $requires = array("css" => array(), "js" => array());

  public static function requireResource(Dispatcher $source, $resource, $type = 'css')
  {
    if(substr($resource, 0, 7) == 'http://'
    || substr($resource, 0, 8) == 'https://'
    || substr($resource, 0, 3) == '://'
    )
    {
      $uri = $resource;
    }
    else
    {
      $uri = $source->getDispatchFabricator()->resource($resource);
    }
    $item = array(
      'resource' => $resource,
      'uri'      => $uri
    );

    if(\in_array($type, array('css', 'js')))
    {
      self::$requires[$type][] = $item;
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
      $out[] = $res['uri'];
    }
    return $out;
  }
}
