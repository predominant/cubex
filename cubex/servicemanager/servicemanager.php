<?php
/**
 * User: brooke.bryan
 * Date: 03/01/13
 * Time: 10:18
 * Description:
 */
namespace Cubex\ServiceManager;

/**
 * Container for services
 */

class ServiceManager
{
  protected $services = [];
  protected $shared = [];

  /**
   * @param $name
   *
   * @return Service
   * @throws \InvalidArgumentException
   */
  public function get($name)
  {
    if($this->exists($name))
    {
      if(isset($this->shared[$name]) && $this->shared[$name] !== null)
      {
        return $this->shared[$name];
      }
      else
      {
        return $this->create($name);
      }
    }
    else
    {
      throw new \InvalidArgumentException("Service does not exist");
    }
  }

  /**
   * @param $name
   *
   * @return bool
   */
  public function exists($name)
  {
    return isset($this->services[$name]);
  }

  /**
   * @param               $name
   * @param ServiceConfig $config
   * @param bool          $shared
   *
   * @return $this
   * @throws \Exception
   */
  public function register($name, ServiceConfig $config, $shared = true)
  {
    if($this->exists($name))
    {
      throw new \Exception("Service already exists");
    }

    $this->services[$name] = array('config' => $config, 'shared' => $shared);
    return $this;
  }

  /**
   * @param $name
   *
   * @return Service
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  protected function create($name)
  {
    if(isset($this->services[$name]))
    {
      $config = $this->services[$name]['config'];
      if($config instanceof ServiceConfig)
      {
        $factory = $config->getFactory();
        $service = $factory();
        if($service instanceof Service)
        {
          $service->configure($config);
        }
        else
        {
          throw new \Exception("Invalid service factory");
        }

        if($this->services[$name]['shared'])
        {
          return $this->shared[$name] = $service;
        }
        else
        {
          return $service;
        }
      }
      else
      {
        throw new \Exception("Invalid service details");
      }
    }
    else
    {
      throw new \InvalidArgumentException("Service does not exist");
    }
  }
}
