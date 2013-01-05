<?php
/**
 * User: brooke.bryan
 * Date: 05/01/13
 * Time: 14:39
 * Description:
 */

namespace Cubex\Component;

use Cubex\Config\Config;

/**
 * Standard component
 */
interface Component
{
  public function init();

  /**
   * Accept the manual configuration for the component and add any default values for missing items
   *
   * @param \Cubex\Config\Config $configuration
   *
   * @return \Cubex\Config\Config
   */
  public function configure(Config $configuration);
}
