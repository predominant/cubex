<?php
/**
 * @author: gareth.evans
 */
namespace Cubex\Platform;

abstract class DetectionAbstract
{
  protected $_detection;

  public function __construct($className, $classDir, $fileName)
  {
    $vendorPath = CUBEX_ROOT . DIRECTORY_SEPARATOR . 'vendor/';

    try
    {
      $include = include_once $vendorPath . $classDir . $fileName;
      if($include === false)
      {
        throw new \RuntimeException();
      }
    }
    catch (\RuntimeException $e)
    {
      throw new \RuntimeException("{$fileName} Not found");
    }

    $this->_detection = new $className();
  }
}
