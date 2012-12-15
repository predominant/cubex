<?php
/**
 * User: brooke.bryan
 * Date: 15/12/12
 * Time: 11:39
 * Description:
 */

namespace Cubex\Dispatch;

/**
 * Respond to dispatch requests
 */
use Cubex\Http\Response;
use Cubex\Cubex;
use Cubex\Base\ErrorPage;

class Respond
{
  protected $_cache_time = 2592000; //60 * 60 * 24 * 30

  protected $_use_map = true;
  protected $_entity_map = array();
  protected $_domain_map = array();

  /**
   * @param array $entity_map
   * @param array $domain_map
   */
  public function __construct($entity_map = array(), $domain_map = array())
  {
    $this->_entity_map = $entity_map;
    $this->_domain_map = $domain_map;
  }

  /**
   * Process dispatch path and return
   *
   * Path is created from domainhash/entityhash/type(;debug)/relative_path
   *
   * @param $path
   *
   * @return Response
   */
  public function getResponse($path)
  {
    list($domain_hash, $entity_hash, $type, $rel) = \explode('/', $path, 4);

    list($type, $debug) = \explode(';', $type, 2);

    if($type == 'pamon')
    {
      $this->_use_map = false;
    }

    /**
     * If the client already has the content, no need to make the server work
     */
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $debug != 'nocache' && $type != 'pamon')
    {
      $response = new Response("");
      $response->addHeader("X-Powered-By", "Cubex:Dispatch");
      $response->setStatus(304);
      $response->setCacheable($this->_cache_time); //Dispatch content should never change
      $response->setLastModified(time());
      return $response;
    }

    $domain        = $this->getDomain($domain_hash);
    $entity_path   = $this->getEntityPath($entity_hash);
    $resource_type = \end(\explode('.', $rel));

    if($type != 'pkg')
    {
      $type = 'static';
    }

    /**
     * Stop possible hacks for disk paths, e.g. /js/../../../etc/passwd
     */
    if(\preg_match('@(//|\.\.)@', $rel))
    {
      return new Response(new ErrorPage(400));
    }

    $types = $this->supportedTypes();

    /**
     * Either hack attempt or a dev needs a slapped wrist
     */
    if(empty($types[$resource_type]))
    {
      return new Response(new ErrorPage(404));
    }

    if($type == 'pkg')
    {
      $data = $this->getPackageData($entity_path, $rel, $domain, $type);
    }
    else
    {
      $data = $this->getData($entity_path, $rel, $domain, $type);
    }

    /**
     * No data found, assume 404
     */
    if(empty($data))
    {
      return new Response(new ErrorPage(404));
    }

    $response = new Response($data);
    $response->addHeader("Content-Type", $types[$resource_type]);
    $response->addHeader("X-Powered-By", "Cubex:Dispatch");
    $response->setStatus(200);
    if($debug != 'nocache')
    {
      $response->setCacheable($this->_cache_time);
      $response->setLastModified(time());
    }
    return $response;
  }

  /**
   * Load file data or compile package for the response
   */
  public function getData($entity_path, $file_path, $domain = null)
  {
    $base_path = Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR . $entity_path;

    if($domain !== null && !empty($domain))
    {
      $locate_list  = array();
      $domain_parts = \explode('.', $domain);
      $domain_path  = '';
      foreach($domain_parts as $dpart)
      {
        //Prepend with . on domain to avoid conflicts in standard resources
        $domain_path .= '.' . $dpart;
        $locate_list[] = $base_path . DIRECTORY_SEPARATOR . $domain_path . DIRECTORY_SEPARATOR . $file_path;
      }
      $locate_list = \array_reverse($locate_list);
    }

    $locate_list[] = $base_path . DIRECTORY_SEPARATOR . $file_path;

    foreach($locate_list as $file)
    {
      try
      {
        $data = file_get_contents($file);
        if(!empty($data))
        {
          return $this->minifyData($data, \end(\explode('.', $file_path)));
        }
      }
      catch(\Exception $e)
      {
      }
    }
    return "";
  }

  /**
   * Compile package from map
   *
   * @param $entity_path
   * @param $file_path
   * @param $domain
   *
   * @return string
   */
  public function getPackageData($entity_path, $file_path, $domain)
  {
    $base_path = Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR . $entity_path;

    $response = '';

    try
    {
      $resources = \parse_ini_file($base_path . DIRECTORY_SEPARATOR . 'dispatch.ini', false);
    }
    catch(\Exception $e)
    {
      $resources = false;
    }

    if(!$resources)
    {
      $resources = Mapper::mapDirectory($base_path);
      if($this->_use_map)
      {
        Mapper::saveMap($resources, $base_path);
      }
    }

    $match_ext = \end(\explode('.', $file_path));

    /**
     * Only allow JS & CSS packages
     */
    if(\in_array($match_ext, array("js", "css")))
    {
      if(!empty($resources))
      {
        foreach($resources as $resource => $checksum)
        {
          if(\end(\explode('.', $resource)) == $match_ext)
          {
            $response .= $this->getData($entity_path, $resource, $domain) . "\n";
          }
        }
      }
    }

    return $response;
  }

  /**
   * Locate path to entity src directory
   *
   * @param string $entity_hash
   *
   * @return string
   */
  public function getEntityPath($entity_hash = '')
  {
    if($entity_hash == 'esabot')
    {
      return 'src';
    }
    else if(isset($this->_entity_map[$entity_hash]))
    {
      return $this->_entity_map[$entity_hash];
    }
    else
    {
      $path = $this->locateEntityPath('', $entity_hash);
      if($path === null)
      {
        return \rawurldecode($entity_hash);
      }
      else
      {
        return $path;
      }
    }
  }

  /**
   * Attempt to match entity based on filesystem
   *
   * @param     $path
   * @param     $match
   * @param int $depth
   *
   * @return null|string
   */
  public function locateEntityPath($path, $match, $depth = 0)
  {
    $base      = Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR;
    $match_len = strlen($match);

    if($handle = \opendir($base . $path))
    {
      while(false !== ($filename = \readdir($handle)))
      {
        if(\substr($filename, 0, 1) == '.') continue;

        if(substr(md5($path . '/' . $filename . '/src'), 0, $match_len) == $match)
        {
          return $path . '/' . $filename . '/src';
        }

        if($depth == 0 && \is_dir($base . $path . DIRECTORY_SEPARATOR . $filename))
        {
          $match = $this->locateEntityPath($path . (empty($path) ? '' : DIRECTORY_SEPARATOR) . $filename, $match, 1);
          if($match !== null)
          {
            return $match;
          }
        }
      }

      \closedir($handle);
    }
    return null;
  }

  /**
   * Get domain from hash, or failover to the current domain processing the request
   *
   * @param $domain_hash
   *
   * @return mixed
   */
  protected function getDomain($domain_hash = '')
  {
    if(isset($this->_domain_map[$domain_hash]))
    {
      return $this->_domain_map[$domain_hash];
    }
    else
    {
      return Cubex::request()->getDomain() . '.' . Cubex::request()->getTld();
    }
  }

  /**
   * Supported file types that can be processed using dispatch
   *
   * @return array
   */
  protected function supportedTypes()
  {
    return array(
      'css' => 'text/css; charset=utf-8',
      'js'  => 'text/javascript; charset=utf-8',
      'png' => 'image/png',
      'jpg' => 'image/jpg',
      'gif' => 'image/gif',
      'swf' => 'application/x-shockwave-flash',
    );
  }

  /**
   * Process file data for minified response
   *
   * @param $data
   * @param $filetype
   *
   * @return string
   */
  protected function minifyData($data, $filetype)
  {
    if(strpos($data, '@' . 'do-not-minify') !== false)
    {
      return $data;
    }

    switch($filetype)
    {
      case 'css':
        // Remove comments.
        $data = preg_replace('@/\*.*?\*/@s', '', $data);
        // Remove whitespace around symbols.
        $data = preg_replace('@\s*([{}:;,])\s*@', '\1', $data);
        // Remove unnecessary semicolons.
        $data = preg_replace('@;}@', '}', $data);
        // Replace #rrggbb with #rgb when possible.
        $data = preg_replace('@#([a-f0-9])\1([a-f0-9])\2([a-f0-9])\3@i', '#\1\2\3', $data);
        $data = trim($data);
        break;
      case 'js':
        //Strip Comments
        $data = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $data);
        $data = preg_replace('!^([\t ]+)?\/\/.+$!m', '', $data);
        //remove tabs, spaces, newlines, etc.
        $data = str_replace(array("\t"), ' ', $data);
        $data = str_replace(array("\r\n", "\r", "\n", '  ', '    ', '    '), '', $data);
        break;
    }

    return $data;
  }
}
