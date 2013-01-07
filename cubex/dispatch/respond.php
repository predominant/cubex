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
use Cubex\Base\Dispatchable;
use Cubex\Http\Request;
use Cubex\Http\Response;
use Cubex\Cubex;
use Cubex\Response\ErrorPage;

class Respond implements Dispatchable
{
  protected $_cacheTime = 2592000; //60 * 60 * 24 * 30

  protected $_useMap = true;
  protected $_entityMap = array();
  protected $_domainMap = array();

  protected $_domainHash;
  protected $_entityHash;
  protected $_dispatchPath;

  /**
   * @param array       $entityMap
   * @param array       $domainMap
   * @param null|string $dispatchPath
   */
  public function __construct($entityMap = array(), $domainMap = array(), $dispatchPath = null)
  {
    $this->_entityMap    = $entityMap;
    $this->_domainMap    = $domainMap;
    $this->_dispatchPath = $dispatchPath;
  }

  public function dispatch(Request $request, Response $response)
  {
    Cubex::setShutdownDetails(false);
    $response->addHeader("Vary", "Accept-Encoding");
    return $this->getResponse($this->_dispatchPath, $response);
  }

  /**
   * Process dispatch path and return
   *
   * Path is created from domainhash/entityhash/type(;debug)/relative_path
   *
   * @param          $path
   * @param Response $response
   *
   * @return Response
   */
  public function getResponse($path, Response $response)
  {
    list($domainHash, $entityHash, $type, $rel) = \explode('/', $path, 4);
    $this->_domainHash = $domainHash;
    $this->_entityHash = $entityHash;

    list($type, $debug) = \explode(';', $type, 2);

    if($type == 'pamon')
    {
      $this->_useMap = false;
    }

    /**
     * If the client already has the content, no need to make the server work
     */
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $debug != 'nocache' && $type != 'pamon')
    {
      $response->addHeader("X-Powered-By", "Cubex:Dispatch");
      $response->setStatus(304);
      $response->setCacheable($this->_cacheTime); //Dispatch content should never change
      $response->setLastModified(time());
      return $response;
    }

    $domain       = $this->getDomain($domainHash);
    $entityPath   = $this->getEntityPath($entityHash);
    $resourceType = \end(\explode('.', $rel));

    if($type != 'pkg')
    {
      $type = 'static';
    }

    /**
     * Stop possible hacks for disk paths, e.g. /js/../../../etc/passwd
     */
    if(\preg_match('@(//|\.\.)@', $rel))
    {
      return $response->webpage(new ErrorPage(400));
    }

    $types = $this->supportedTypes();

    /**
     * Either hack attempt or a dev needs a slapped wrist
     */
    if(empty($types[$resourceType]))
    {
      return $response->webpage(new ErrorPage(404, "Invalid Resource Type"));
    }

    if($type == 'pkg')
    {
      $data = $this->getPackageData($entityPath, $rel, $domain, $type);
    }
    else
    {
      $data = $this->getData($entityPath, $rel, $domain, $type);
    }

    /**
     * No data found, assume 404
     */
    if(empty($data))
    {
      return $response->webpage(new ErrorPage(404, "No Data Found"));
    }

    $response->fromSource($data);
    $response->addHeader("Content-Type", $types[$resourceType]);
    $response->addHeader("X-Powered-By", "Cubex:Dispatch");
    $response->setStatus(200);
    if($debug == 'nocache'
    || (!$this->_useMap && Cubex::config('general')->getBool("debug", false))
    )
    {
      $response->disbleCache();
    }
    else
    {
      $response->setCacheable($this->_cacheTime);
      $response->setLastModified(time());
    }
    return $response;
  }

  /**
   * Load file data or compile package for the response
   */
  public function getData($entityPath, $filePath, $domain = null)
  {
    $basePath = Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR . $entityPath;

    $filePathParts = \explode("/", $filePath);
    $filename = \array_pop($filePathParts);
    $filenameExtension = \end(\explode(".", $filename));
    $filenames = Fabricate::getAllFilenamesOrdered($filename);
    $filenamesOrder = \array_keys($filenames);
    $subDirectory = \implode("/", $filePathParts);
    $locateList  = array();
    foreach($filenamesOrder as $filenameOrder)
    {
      $locateList[$filenameOrder] = array();
    }

    if($domain !== null && !empty($domain))
    {
      $domainParts = \explode('.', $domain);
      $domainPath  = '';
      $filenamesOrderReverse = \array_reverse($filenamesOrder);

      foreach($domainParts as $dpart)
      {
        // Prepend with . on domain to avoid conflicts in standard resources
        $domainPath .= '.' . $dpart;
        $locateFilePath = $basePath . DIRECTORY_SEPARATOR . $domainPath;
        $locateFilePath .= DIRECTORY_SEPARATOR . $subDirectory;
        $locateFilePath .= DIRECTORY_SEPARATOR;

        foreach($filenamesOrderReverse as $fileKey)
        {
          $locateList[$fileKey][] = $locateFilePath . $filenames[$fileKey];
        }
      }

      foreach($filenamesOrderReverse as $fileKey)
      {
        $locateList[$fileKey] = \array_reverse($locateList[$fileKey]);
      }
    }

    $locateFilePath = $basePath . DIRECTORY_SEPARATOR . $subDirectory;
    $locateFilePath .= DIRECTORY_SEPARATOR;
    foreach($filenamesOrder as $fileKey)
    {
      $locateList[$fileKey][] = $locateFilePath . $filenames[$fileKey];
    }

    $data = "";
    $locatedFileKeys = array();

    foreach($locateList as $fileKey => $files)
    {
      foreach($files as $file)
      {
        if(\array_key_exists($fileKey, $locatedFileKeys))
        {
          continue;
        }

        try
        {
          $fileData = \file_get_contents($file);

          if(!empty($fileData))
          {
            $data .= $this->dispatchContent($fileData);
          }

          if($fileData !== false)
          {
            $locatedFileKeys[$fileKey] = true;
          }
        }
        catch(\Exception $e)
        {
        }
      }
    }

    if(!empty($data))
    {
      $data = $this->minifyData($data, $filenameExtension);
    }

    return $data;
  }

  /**
   * Compile package from map
   *
   * @param $entityPath
   * @param $filePath
   * @param $domain
   *
   * @return string
   */
  public function getPackageData($entityPath, $filePath, $domain)
  {
    $basePath = Cubex::core()->projectBasePath(
    ) . DIRECTORY_SEPARATOR . 'cubex' . DIRECTORY_SEPARATOR . $entityPath;

    $response = '';

    try
    {
      $resources = \parse_ini_file($basePath . DIRECTORY_SEPARATOR . 'dispatch.ini', false);
    }
    catch(\Exception $e)
    {
      $resources = false;
    }

    if(!$resources)
    {
      $resources = (new Mapper())->mapDirectory($basePath);
      if($this->_useMap)
      {
        Mapper::saveMap($resources, $basePath);
      }
    }

    $matchExt = \end(\explode('.', $filePath));

    /**
     * Only allow JS & CSS packages
     */
    if(\in_array($matchExt, array("js", "css")))
    {
      if(!empty($resources))
      {
        $resources = array_keys($resources);
        foreach($resources as $resource)
        {
          if(\end(\explode('.', $resource)) == $matchExt)
          {
            $response .= $this->getData($entityPath, $resource, $domain) . "\n";
          }
        }
      }
    }

    return $response;
  }

  /**
   * Locate path to entity src directory
   *
   * @param string $entityHash
   *
   * @return string
   */
  public function getEntityPath($entityHash = '')
  {
    if(in_array($entityHash, ['esabot']))
    {
      return 'cubex/src';
    }
    else if(isset($this->_entityMap[$entityHash]))
    {
      return $this->_entityMap[$entityHash];
    }
    else
    {
      $path = $this->locateEntityPath('', $entityHash);
      if($path === null)
      {
        return \rawurldecode($entityHash);
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
    $base     = Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR;
    $matchLen = \strlen($match);

    if($handle = \opendir($base . $path))
    {
      while(false !== ($filename = \readdir($handle)))
      {
        if(\substr($filename, 0, 1) == '.') continue;

        if(\substr(\md5($path . '/' . $filename . '/src'), 0, $matchLen) == $match)
        {
          return $path . '/' . $filename . '/src';
        }
        else if($depth == 2)
        {
          $opath = $path;
          list(, $path) = explode('/', $path, 2);
          if(\substr(\md5($path . '/' . $filename . '/src'), 0, $matchLen) == $match)
          {
            return $path . '/' . $filename . '/src';
          }
          $path = $opath;
        }

        if($depth < 2 && \is_dir($base . $path . DIRECTORY_SEPARATOR . $filename))
        {
          $matched = $this->locateEntityPath(
            $path . (empty($path) ? '' : DIRECTORY_SEPARATOR) . $filename, $match, $depth + 1
          );
          if($matched !== null)
          {
            return $matched;
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
   * @param $domainHash
   *
   * @return mixed
   */
  protected function getDomain($domainHash = '')
  {
    if(isset($this->_domainMap[$domainHash]))
    {
      return $this->_domainMap[$domainHash];
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
      'ico' => 'image/x-icon',
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
   * @param $fileType
   *
   * @return string
   */
  protected function minifyData($data, $fileType)
  {
    if(\strpos($data, '@' . 'do-not-minify') !== false)
    {
      return $data;
    }

    switch($fileType)
    {
      case 'css':
        // Remove comments.
        $data = \preg_replace('@/\*.*?\*/@s', '', $data);
        // Remove whitespace around symbols.
        $data = \preg_replace('@\s*([{}:;,])\s*@', '\1', $data);
        // Remove unnecessary semicolons.
        $data = \preg_replace('@;}@', '}', $data);
        // Replace #rrggbb with #rgb when possible.
        $data = \preg_replace('@#([a-f0-9])\1([a-f0-9])\2([a-f0-9])\3@i', '#\1\2\3', $data);
        $data = trim($data);
        break;
      case 'js':
        //Strip Comments
        $data = \preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $data);
        $data = \preg_replace('!^([\t ]+)?\/\/.+$!m', '', $data);
        //remove tabs, spaces, newlines, etc.
        $data = \str_replace(array("\t"), ' ', $data);
        $data = \str_replace(array("\r\n", "\r", "\n", '  ', '    ', '    '), '', $data);
        break;
    }

    return $data;
  }

  /**
   * Dispatch nested images
   *
   * @param $data
   *
   * @return mixed
   */
  public function dispatchContent($data)
  {
    $data = preg_replace_callback(
      '@url\s*\((\s*[\'"]?.*?)\)@s', array($this, "dispatchUri"), $data
    );

    return $data;
  }

  /**
   * Calculate nested images
   *
   * @param $data
   *
   * @return string
   */
  public function dispatchUri($data)
  {
    $fab = new Fabricate();

    $uri = \trim($data[1], "'\" \r\t\n");

    if(\in_array(substr($uri, 0, 7), ['data:im', 'http://', 'https:/']))
    {
      return "url('" . $uri . "')";
    }

    $entityHash = $this->_entityHash;

    if(\substr($uri, 0, 1) == '/')
    {
      $uri        = \substr($uri, 1);
      $entityHash = 'esabot';
    }

    $resources    = false;
    $resourceHash = 'pamon';

    try
    {
      $map = $this->getEntityPath($entityHash);
      if($map)
      {
        $basePath  = Cubex::core()->projectBasePath() . DIRECTORY_SEPARATOR . $map;
        $resources = \parse_ini_file($basePath . DIRECTORY_SEPARATOR . 'dispatch.ini', false);
      }
    }
    catch(\Exception $e)
    {
    }

    $ext = \end(\explode('.', $uri));
    if($ext == 'css')
    {
      $uri = "css/" . $uri;
    }
    else if($ext == 'js')
    {
      $uri = "js/" . $uri;
    }
    else
    {
      $uri = "img/" . $uri;
    }

    if($resources)
    {
      if(isset($resources[$uri]))
      {
        $resourceHash = $fab->generateResourceHash($resources[$uri]);
      }
    }

    $parts = array(
      $this->_domainHash,
      $entityHash,
      $resourceHash,
      $uri,
    );

    return "url('/res/" . \implode('/', $parts) . "')";
  }
}
