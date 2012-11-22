<?php
/**
 * User: brooke.bryan
 * Date: 22/11/12
 * Time: 18:59
 * Description:
 */
namespace Cubex\Base;

abstract class AppLoader
{

  public static function load(\Cubex\Http\Request $request)
  {
    $application = static::getBySubAndPath($request->getSubDomain(), $request->getPath());

    if($application === null)
    {
      $application = static::getBySubDomain($request->getSubDomain());
    }

    if($application === null)
    {
      $application = static::getByPath($request->getPath());
    }

    if($application === null)
    {
      $application = static::defaultApplication();
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

  public static function defaultApplication()
  {
    return '';
  }

  public static function getBySubAndPath($subdomain, $path)
  {
    switch($subdomain)
    {
      default:
        switch($path)
        {
        }
        break;
    }
    return null;
  }

  public static function getBySubDomain($subdomain)
  {
    switch($subdomain)
    {
    }
    return null;
  }

  public static function getByPath($path)
  {
    switch($path)
    {
    }
    return null;
  }
}
