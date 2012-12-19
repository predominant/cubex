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
  protected $_realPath;
  protected $_path;

  protected static $_baseMap = null;

  protected $_entityMap = null;

  protected $_entityHash = null;
  protected $_domainHash = null;

  /**
   * Initiate a new Fabricate class for each entity
   *
   * @param null $entityBase
   */
  public function __construct($entityBase = null)
  {
    $entityBase        = \rtrim($entityBase, '/') . '/';
    $this->_realPath   = Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR . $entityBase . 'src';
    $this->_path        = $entityBase . 'src';
    $this->_entityHash = $this->generateEntityHash($this->_path);
  }

  /**
   * Get the entity hash for current dispatcher
   *
   * @return string
   */
  public function getEntityHash()
  {
    return $this->_entityHash;
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
    $this->_entityHash = $hash;
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
    $this->_domainHash = $hash;
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
   * @param $entityPath
   *
   * @return string
   */
  public static function generateEntityHash($entityPath)
  {
    return \substr(\md5($entityPath), 0, 6);
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
    if($this->_domainHash === null)
    {
      $domain             = Cubex::request()->getDomain() . '.' . Cubex::request()->getTld();
      $this->_domainHash = $this->generateDomainHash($domain);
    }

    if(!$base && $this->_entityHash === null)
    {
      $this->_entityHash = $this->generateEntityHash($this->_path);
    }

    return \implode(
      '/', array('',
                 Cubex::config("dispatch")->getStr('base', 'res'),
                 $this->_domainHash,
                 $base ? 'esabot' : $this->_entityHash
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
    return \implode('/', array($this->preHash($base), 'pkg', $name . '.' . $ext));
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

    $base          = \substr($path, 0, 1) == '/';
    $path          = \ltrim($path, '/');
    $resourceHash = 'pamon'; //No Map

    if($base)
    {
      if(self::$_baseMap === null)
      {
        $this->loadBaseMap();
      }
      if(isset(self::$_baseMap[$path]))
      {
        $resourceHash = $this->generateResourceHash(self::$_baseMap[$path]);
      }
    }
    else
    {
      if($this->_entityMap === null)
      {
        $this->loadEntityMap();
      }

      if(isset($this->_entityMap[$path]))
      {
        $resourceHash = $this->generateResourceHash($this->_entityMap[$path]);
      }
    }

    return \implode('/', array($this->preHash($base), $resourceHash, $path));
  }

  /**
   * @return Fabricate
   */
  protected function loadEntityMap()
  {
    try
    {
      $this->_entityMap = \parse_ini_file($this->_realPath . DIRECTORY_SEPARATOR . 'dispatch.ini', false);
    }
    catch(\Exception $e)
    {
      $this->_entityMap = array();
    }
    return $this;
  }

  /**
   * Load the base project dispatch resource map
   *
   * @return Fabricate
   */
  protected function loadBaseMap()
  {
    try
    {
      self::$_baseMap = \parse_ini_file(
        Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR . 'src/dispatch.ini', false
      );
    }
    catch(\Exception $e)
    {
      self::$_baseMap = array();
    }
    return $this;
  }
}
