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
   * @param ServiceFactory $factory
   *
   * @return $this
   */
  public function setFactory(ServiceFactory $factory)
  {
    $this->_factory = $factory;
    return $this;
  }

  /**
   * @return ServiceFactory
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
    $factory = $config->getStr("factory");
    if($factory !== null)
    {
      $this->setFactory(new $factory());
    }

    foreach($config as $k => $v)
    {
      if(!in_array($k, ['factory', 'register_service_as']))
      {
        $this->$k = $v;
      }
    }

    return $this;
  }
}
