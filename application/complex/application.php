<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 19:40
 * Description:
 */

namespace Cubex\Application\Complex;

class ComplexApplication extends \Cubex\Application\App
{
  public function getName()
  {
    return "Sample Complex Application";
  }

  public function getDescription()
  {
    return "Complex application to demonstrate full functionality of the Cubex framework";
  }

  public function requiredApplications()
  {
    return array('simple');
  }

  public function getBaseURI()
  {
    return "/complex/";
  }

  public function getDefaultController()
  {
    return 'ComplexController';
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
