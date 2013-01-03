<?php
/**
 * User: brooke.bryan
 * Date: 03/01/13
 * Time: 12:49
 * Description:
 */
namespace Cubex\Database;

use Cubex\Database\MySQL\MySQL;
use Cubex\ServiceManager\ServiceFactory;
use Cubex\ServiceManager\ServiceConfig;

/**
 * Database Factory
 */
class Factory implements ServiceFactory
{
  /**
   * @param \Cubex\ServiceManager\ServiceConfig $config
   *
   * @return \Cubex\Database\Database
   * @throws \Exception
   */
  public function createService(ServiceConfig $config)
  {
    switch($config->getStr('engine', 'mysql'))
    {
      case 'mysql':
        return new MySQL();
    }
    throw new \Exception("Invalid service configuration");
  }
}
