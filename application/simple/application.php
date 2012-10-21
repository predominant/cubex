<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 19:40
 * Description:
 */

namespace Cubex\Application\Simple;

class Application extends \Cubex\Base\Application
{
  public function getName()
  {
    return "Simple Application";
  }

  public function getDescription()
  {
    return "Simple application to demonstrate minimal functionality of the Cubex framework";
  }

  public function requiredApplications()
  {
    return array();
  }

  public function getBaseURI()
  {
    return "/simple/";
  }

  public function getDefaultController()
  {
    return 'DefaultController';
  }

  public function getRoutes()
  {
    return array();
  }

  public function registerAutoLoader()
  {
    return null;
  }
}
