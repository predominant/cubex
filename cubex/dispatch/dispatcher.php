<?php
/**
 * User: brooke.bryan
 * Date: 15/12/12
 * Time: 18:22
 * Description:
 */
namespace Cubex\Dispatch;

/**
 * Base for any dispatchable entity
 */
use Cubex\Application\Layout;
use \Cubex\Project\Application;

abstract class Dispatcher
{
  private $_fabrication;
  protected $_entityDispatchName;

  /**
   * Create the dispatch name e.g. Application Name
   *
   * @return string
   */
  public function dispatcherEntityName()
  {
    if($this->_entityDispatchName !== null)
    {
      return $this->_entityDispatchName;
    }

    if($this instanceof Layout)
    {
      $class = Application::getApp();
    }
    else
    {
      $class = \get_class($this);
    }

    $reflector = new \ReflectionClass($class);
    $parts     = \explode('\\', $reflector->getName());
    \array_shift($parts);
    $parts                     = \array_chunk($parts, 1, false);
    $this->_entityDispatchName = \strtolower($parts[1][0]);
    return $this->_entityDispatchName;
  }

  /**
   * Require all $type files with the request
   *
   * @param string $type
   *
   * @return Dispatcher
   */
  public function requirePackage($type = 'css')
  {
    Prop::requirePackage($this, $this->dispatcherEntityName(), $type);
    return $this;
  }

  /**
   * Specify a CSS file to include (/css | .css are not required)
   *
   * @param $file
   *
   * @return Dispatcher
   */
  public function requireCss($file)
  {
    if(!$this->isExternalUri($file))
    {
      if(\substr($file, -4) != '.css')
      {
        $file = $file . '.css';
      }
      if(\substr($file, 0, 1) == '/')
      {
        $file = '/css' . $file;
      }
      else
      {
        $file = 'css/' . $file;
      }
    }
    Prop::requireResource($this, $file, 'css');
    return $this;
  }

  /**
   * Specify a JS file to include (/js | .js are not required)
   *
   * @param $file
   *
   * @return Dispatcher
   */
  public function requireJs($file)
  {
    if(!$this->isExternalUri($file))
    {
      if(\substr($file, -3) != '.js')
      {
        $file = $file . '.js';
      }
      if(\substr($file, 0, 1) == '/')
      {
        $file = '/js' . $file;
      }
      else
      {
        $file = 'js/' . $file;
      }
    }
    Prop::requireResource($this, $file, 'js');
    return $this;
  }

  /**
   * Build a full image URI (no need to prefix with img)
   *
   * @param $file
   *
   * @return string
   */
  public function imgUri($file)
  {
    if(!$this->isExternalUri($file))
    {
      if(\substr($file, 0, 1) == '/')
      {
        $file = '/img' . $file;
      }
      else
      {
        $file = 'img/' . $file;
      }
    }
    return $this->getDispatchFabricator()->resource($file);
  }

  /**
   * @return Fabricate
   */
  public function getDispatchFabricator()
  {
    if($this->_fabrication === null)
    {
      $this->_fabrication = new Fabricate($this->baseRelPath());
    }
    return $this->_fabrication;
  }

  /**
   * get the base relative path to the project, e.g. applications/frontend/
   *
   * @return string
   */
  public function baseRelPath()
  {
    if($this instanceof Layout)
    {
      $class = Application::getApp();
    }
    else
    {
      $class = \get_class($this);
    }
    $reflector = new \ReflectionClass($class);
    $parts     = \explode('\\', $reflector->getName());
    $parts = \array_chunk($parts, 3, false);
    return \strtolower(\implode('/', $parts[0])) . '/';
  }

  /**
   * Return Dispatch Uri for the resource
   *
   * @param $resource
   *
   * @return string
   */
  public function resourceUri($resource)
  {
    return $this->getDispatchFabricator()->resource($resource);
  }

  /**
   * Return Dispatch Uri for entity package of a specific type
   *
   * @param string $type
   *
   * @return string
   */
  public function packageUri($type = 'css')
  {
    return $this->getDispatchFabricator()->package($this->dispatcherEntityName(), $type);
  }

  /**
   * Determine if a resource is external
   *
   * @param $resource
   *
   * @return bool
   */
  public function isExternalUri($resource)
  {
    return (
    substr($resource, 0, 2) == '//'
    || substr($resource, 0, 7) == 'http://'
    || substr($resource, 0, 8) == 'https://'
    );
  }
}
