<?php
/**
 * User: brooke.bryan
 * Date: 03/01/13
 * Time: 12:26
 * Description:
 */

namespace Cubex\ServiceManager;

/**
 * Service Factory
 */
interface ServiceFactory
{
  /**
   * @param ServiceConfig $config
   *
   * @return mixed
   */
  public function createService(ServiceConfig $config);
}
