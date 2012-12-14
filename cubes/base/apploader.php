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

/**
 * The app loader is essentiall a "project" router, defining which application should handle a request
 */
abstract class AppLoader
{

  /**
   * Initialise the correct application for the request, processing sub methods to calculate the correct application
   *
   * Processed in order: getBySubAndPath, getBySubDomain, getByPath, defaultApplication
   *
   * If an application cannot be found, a cubex fatal be triggered
   *
   * @param \Cubex\Http\Request $request
   */
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

  /**
   * Provide a default application for the project if no specific application can be found.
   *
   * @return Application|null
   */
  public static function defaultApplication()
  {
    return null;
  }

  /**
   * Calculate the application based on the sub domain and path in the request
   *
   * @param $subdomain
   * @param $path
   *
   * @return Application|null
   */
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

  /**
   * Calculate the application based on the sub domain from the request
   *
   * @param $subdomain
   *
   * @return Application|null
   */
  public static function getBySubDomain($subdomain)
  {
    switch($subdomain)
    {
    }
    return null;
  }

  /**
   * Calculate the application based on the path from the request
   *
   * @param $path
   *
   * @return Application|null
   */
  public static function getByPath($path)
  {
    switch($path)
    {
    }
    return null;
  }
}
