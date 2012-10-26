<?php
/**
 * User: brooke.bryan
 * Date: 21/10/12
 * Time: 15:41
 * Description:
 */

namespace Cubex\Application;

class Loader
{

  final public static function load(\Cubex\Http\Request $request)
  {
    $application = self::getBySubAndPath($request->getSubDomain(), $request->getPath());

    if($application === null)
    {
      $application = self::getBySubDomain($request->getSubDomain());
    }

    if($application === null)
    {
      $application = self::getByPath($request->getPath());
    }

    if($application === null)
    {
      $application = self::defaultApplication();
    }

    if($application !== null)
    {
      \Cubex\Base\Application::initialise($application);
    }
    else
    {
      \Cubex\Cubex::fatal("No application has been defined");
    }
  }

  final public static function defaultApplication()
  {
    return 'Complex';
  }

  final public static function getBySubAndPath($subdomain, $path)
  {
    switch($subdomain)
    {
      case 'www':
        switch($path)
        {
          case '/jay':
            return 'Complex';
            break;
        }
        break;
    }
    return null;
  }

  final public static function getBySubDomain($subdomain)
  {
    switch($subdomain)
    {
      case 'www':
        return 'Simple';
      case 'broken':
        return 'broken';
    }
    return null;
  }

  final public static function getByPath($path)
  {
    switch($path)
    {
      case '/complex':
        return 'Complex';
        break;
    }
    return null;
  }
}
