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
abstract class Dispatcher
{
  private $_fabrication;

  public function requireCss($file)
  {
    if(\substr($file,-4) != '.css')
    {
      $file = $file . '.css';
    }
    if(\substr($file,0,1) == '/')
    {
      $file = '/css' . $file;
    }
    else
    {
      $file = 'css/' . $file;
    }
    Prop::requireResource($this, $file, 'css');
    return $this;
  }

  public function requireJs($file)
  {
    if(\substr($file,-3) != '.js')
    {
      $file = $file . '.js';
    }
    if(\substr($file,0,1) == '/')
    {
      $file = '/js' . $file;
    }
    else
    {
      $file = 'js/' . $file;
    }
    Prop::requireResource($this, $file, 'js');
    return $this;
  }

  public function imgUri($file)
  {
    if(\substr($file,0,1) == '/')
    {
      $file = '/img' . $file;
    }
    else
    {
      $file = 'img/' . $file;
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
    $reflector = new \ReflectionClass(\get_class($this));
    $parts     = \explode('\\', $reflector->getName());
    \array_shift($parts);
    $parts = \array_chunk($parts, 2, false);
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
    $reflector = new \ReflectionClass(\get_class($this));
    $parts     = \explode('\\', $reflector->getName());
    \array_shift($parts);
    $parts = \array_chunk($parts, 1, false);
    return $this->getDispatchFabricator()->package(strtolower($parts[1][0]), $type);
  }
}
