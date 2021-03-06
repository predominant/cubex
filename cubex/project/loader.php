<?php
/**
 * User: brooke.bryan
 * Date: 22/11/12
 * Time: 18:59
 * Description:
 */
namespace Cubex\Project;

use Cubex\Base\Dispatchable;
use Cubex\Cubex;
use Cubex\Http\Response;
use \Cubex\Project\Application;
use \Cubex\Http\Request;
use Cubex\Event\Events;

/**
 * The app loader is essential a "project" router, defining which application should handle a request
 */
abstract class Loader implements Dispatchable
{
  /**
   * @return string
   */
  public function getProjectName()
  {
    return "Unknown";
  }

  /**
   * Initialise the correct application for the request, processing sub methods to calculate the correct application
   *
   * Processed in order: getBySubAndPath, getBySubDomain, getByPath, defaultApplication
   *
   * If an application cannot be found, a cubex fatal be triggered
   *
   * @param Request  $request
   * @param Response $response
   *
   * @return Response
   * @throws \Exception
   */
  public function dispatch(Request $request, Response $response)
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
      return $application->dispatch($request, $response);
    }
    else
    {
      throw new \Exception("No application could be loaded");
    }
  }

  /**
   * Provide a default application for the project if no specific application can be found.
   *
   * @return Application|null
   */
  public function defaultApplication()
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
  public function getBySubAndPath($subdomain, $path)
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
  public function getBySubDomain($subdomain)
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
  public function getByPath($path)
  {
    switch($path)
    {
    }
    return null;
  }
}
