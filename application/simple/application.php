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
    return 'defaultController';
  }

  public function getRoutes()
  {
    return array(
      '/I(?P<id>[1-9]\d*)'                 => 'itemController',
      '/(?P<name>[a-z]*)(?P<id>[0-9]*)' => 'dataController',
      '/basic/'                            =>
      array(
        ''    => 'basicController',
        'com' => 'complexController'
      )
    );
  }

  public function registerAutoLoader()
  {
    return null;
  }
}
