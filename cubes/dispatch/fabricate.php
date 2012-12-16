<?php
/**
 * User: brooke.bryan
 * Date: 15/12/12
 * Time: 17:49
 * Description:
 */
namespace Cubex\Dispatch;

/**
 * Fabricate requests
 */
use Cubex\Cubex;

class Fabricate
{
  protected $_real_path;
  protected $_path;

  protected static $_base_map = null;

  protected $_entity_map = null;

  protected $_entity_hash = null;
  protected $_domain_hash = null;

  /**
   * Initiate a new Fabricate class for each entity
   *
   * @param null $entity_base
   */
  public function __construct($entity_base = null)
  {
    $entity_base      = rtrim($entity_base, '/') . '/';
    $this->_real_path = Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR . $entity_base . 'src';
    $this->_path      = $entity_base . 'src';
  }

  /**
   * Set a custom entity hash
   *
   * @param $hash
   *
   * @return Fabricate
   */
  public function setEntityHash($hash)
  {
    $this->_entity_hash = $hash;
    return $this;
  }

  /**
   * set a custom domain hash
   *
   * @param $hash
   *
   * @return Fabricate
   */
  public function setDomainHash($hash)
  {
    $this->_domain_hash = $hash;
    return $this;
  }

  /**
   * @param $domain
   *
   * @return string
   */
  public static function generateDomainHash($domain)
  {
    return \substr(\md5($domain), 0, 6);
  }

  /**
   * @param $entity_path
   *
   * @return string
   */
  public static function generateEntityHash($entity_path)
  {
    return \substr(\md5($entity_path), 0, 6);
  }

  /**
   * @param $hash
   *
   * @return string
   */
  public function generateResourceHash($hash)
  {
    return \substr($hash, 0, 10);
  }

  /**
   * Generate domain/entity hash to form the base of the uri
   *
   * @param $base bool Base Path
   *
   * @return string
   */
  public function preHash($base = false)
  {
    if($this->_domain_hash === null)
    {
      $domain             = Cubex::request()->getDomain() . '.' . Cubex::request()->getTld();
      $this->_domain_hash = $this->generateDomainHash($domain);
    }

    if(!$base && $this->_entity_hash === null)
    {
      $this->_entity_hash = $this->generateEntityHash($this->_path);
    }

    return \implode(
      '/', array('',
                 Cubex::config("dispatch")->getStr('base', 'res'),
                 $this->_domain_hash,
                 $base ? 'esabot' : $this->_entity_hash
           )
    );
  }

  /**
   * Generate a package uri
   *
   * @param        $name
   * @param string $ext
   * @param bool   $base
   *
   * @return string
   */
  public function package($name, $ext = 'css', $base = false)
  {
    return implode('/', array($this->preHash($base), 'pkg', $name . '.' . $ext));
  }

  /**
   * Create a resource uri
   *
   * @param $path
   *
   * @return string
   */
  public function resource($path)
  {

    $base          = substr($path, 0, 1) == '/';
    $path          = ltrim($path, '/');
    $resource_hash = 'pamon'; //No Map

    if($base)
    {
      if(self::$_base_map === null)
      {
        $this->loadBaseMap();
      }
      if(isset(self::$_base_map[$path]))
      {
        $resource_hash = $this->generateResourceHash(self::$_base_map[$path]);
      }
    }
    else
    {
      if($this->_entity_map === null)
      {
        $this->loadEntityMap();
      }

      if(isset($this->_entity_map[$path]))
      {
        $resource_hash = $this->generateResourceHash($this->_entity_map[$path]);
      }
    }

    return implode('/', array($this->preHash($base), $resource_hash, $path));
  }

  /**
   * @return Fabricate
   */
  protected function loadEntityMap()
  {
    try
    {
      $this->_entity_map = \parse_ini_file($this->_real_path . DIRECTORY_SEPARATOR . 'dispatch.ini', false);
    }
    catch(\Exception $e)
    {
      $this->_entity_map = array();
    }
    return $this;
  }

  protected function loadBaseMap()
  {
    try
    {
      self::$_base_map = \parse_ini_file(
        Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR . 'src/dispatch.ini', false
      );
    }
    catch(\Exception $e)
    {
      self::$_base_map = array();
    }
    return $this;
  }
}
