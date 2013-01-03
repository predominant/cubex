<?php
/**
 * User: brooke.bryan
 * Date: 03/01/13
 * Time: 12:32
 * Description:
 */

namespace Cubex\Locale;

use Cubex\ServiceManager\ServiceFactory;
use Cubex\ServiceManager\ServiceConfig;

/**
 * Locale Factory
 */
class Factory implements ServiceFactory
{
  /**
   * @param ServiceConfig $config
   *
   * @return Locale
   */
  public function createService(ServiceConfig $config)
  {
    return new Locale();
  }
}
