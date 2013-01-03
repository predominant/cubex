<?php
/**
 * User: brooke.bryan
 * Date: 03/01/13
 * Time: 12:49
 * Description:
 */
namespace Cubex\Cache;

use Cubex\Cache\Memcache\Memcache;
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
   * @return \Cubex\Cache\Cache
   */
  public function createService(ServiceConfig $config)
  {
    return new Memcache();
  }
}
