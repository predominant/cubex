<?php
/**
 * User: brooke.bryan
 * Date: 03/01/13
 * Time: 10:50
 * Description:
 */

namespace Cubex\ServiceManager;

/**
 * Standard Service Provider
 */
interface Service
{
  /**
   * @param ServiceConfig $configuration
   *
   * @return mixed
   */
  public function configure(ServiceConfig $configuration);
}
