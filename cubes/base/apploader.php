<?php
/**
 * User: brooke.bryan
 * Date: 22/11/12
 * Time: 18:59
 * Description:
 */
namespace Cubex\Base;

use Cubex\Cubex;
use \Cubex\Base\Application;
use \Cubex\Http\Request;
use Cubex\Events\Events;

abstract class AppLoader
{

  public static function load(Request $request)
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

    if($application !== null && $application instanceof Application)
    {
      Events::trigger(Events::CUBEX_LAUNCH);
      Application::initialise($application);
    }
    else
    {
      Cubex::fatal("No application has been defined");
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
