<?php
/**
 * User: brooke.bryan
 * Date: 03/01/13
 * Time: 10:46
 * Description:
 */

namespace Cubex\ServiceManager;

use Cubex\Config\Config;
use Cubex\Traits\Data\Handler;

/**
 * Service Configuration container
 */

class ServiceConfig implements \IteratorAggregate
{
  use Handler;

  /**
   * @var callable
   */
  protected $_factory;

  /**
   * Callable for generating the service
   *
   * @param callable $factory
   *
   * @return $this
   */
  public function setFactory(callable $factory)
  {
    $this->_factory = $factory;
    return $this;
  }

  /**
   * @return callable
   */
  public function getFactory()
  {
    return $this->_factory;
  }

  /**
   * @param \Cubex\Config\Config $config
   *
   * @return ServiceConfig
   */
  public function fromConfig(Config $config)
  {
    $factory = $config->getArr("factory");
    if($factory !== null)
    {
      $this->setFactory($factory);
    }

    foreach($config as $k => $v)
    {
      $this->$k = $v;
    }

    return $this;
  }
}
