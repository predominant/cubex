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

use Cubex\Cache\Cache;
use Cubex\Database\Database;
use Cubex\Locale\Locale;
use Cubex\Session\Session;

class ServiceManager
{
  protected $services = array();
  protected $shared = array();

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
        $factoryClass = $config->getFactory();
        $factory      = new $factoryClass();
        if($factory instanceof ServiceFactory)
        {
          $service = $factory->createService($config);
        }
        else
        {
          throw new \Exception("Invalid service factory");
        }

        if($service instanceof Service)
        {
          $service->configure($config);
        }
        else
        {
          throw new \Exception("Invalid service created by factory '$factoryClass'");
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

  /**
   * @param string $connection
   *
   * @return \Cubex\Database\Database
   * @throws \Exception
   */
  public function db($connection = 'db')
  {
    $database = $this->get($connection);
    if($database instanceof Database)
    {
      return $database;
    }
    throw new \Exception("No database service available");
  }

  /**
   * @param string $connection
   *
   * @return \Cubex\Cache\Cache
   * @throws \Exception
   */
  public function cache($connection = 'local')
  {
    $cache = $this->get($connection);
    if($cache instanceof Cache)
    {
      return $cache;
    }
    throw new \Exception("No cache service available");
  }

  /**
   * @return \Cubex\Session\Session
   * @throws \Exception
   */
  public function session()
  {
    $session = $this->get("session");
    if($session instanceof Session)
    {
      return $session;
    }
    throw new \Exception("No session service available");
  }

  /**
   * @return \Cubex\Locale\Locale
   * @throws \Exception
   */
  public function locale()
  {
    $session = $this->get("locale");
    if($session instanceof Locale)
    {
      return $session;
    }
    throw new \Exception("No locale service available");
  }
}
