<?php
/**
 * User: brooke.bryan
 * Date: 03/01/13
 * Time: 10:46
 * Description:
 */

namespace Cubex\ServiceManager;

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
}
