<?php
/**
 * User: brooke.bryan
 * Date: 13/10/12
 * Time: 18:33
 * Description: Bootstrap Cubex
 */
namespace Cubex;

/**
 * Cubex Framework
 */
use Cubex\Base\Dispatchable;
use Cubex\Data\Handler;
use Cubex\Event\Event;
use Cubex\Http\Request;
use Cubex\Event\Events;
use Cubex\Response\WebPage;
use Cubex\View\HTMLElement;
use Cubex\Http\Response;
use Cubex\Dispatch\Respond;
use Cubex\Controller\BaseController;

/**
 * Cubex Framework Core
 */
final class Cubex
{

  public static $cubex = null;

  private $_request = null;
  private $_controller = null;
  private $_configuration = null;
  private $_connections = null;
  private $_locale = null;

  private $_projectBase = '';

  private $_allowShutdownDetails = true;

  private $_classmap = array();

  /**
   * Verify and setup the environment for cubex to run in
   */
  final public static function boot()
  {
    if(\defined('PHP_MAJOR_VERSION')) //Do not check version if running through a compiler
    {
      $requiredVersion = '5.4.0';
      $currentVersion  = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
      if($currentVersion < $requiredVersion)
      {
        Cubex::fatal("You are running PHP '" . $currentVersion . "', version '{$requiredVersion}' required");
      }
    }

    $env = \getenv('CUBEX_ENV'); // Apache Config
    if(!$env && isset($_ENV['CUBEX_ENV']))
    {
      $env = $_ENV['CUBEX_ENV'];
    }
    if(!$env)
    {
      Cubex::fatal("The 'CUBEX_ENV' environmental variable is not defined.");
    }

    \register_shutdown_function('Cubex\Cubex::shutdown');
    \set_error_handler('Cubex\Cubex::errorHandler');
    \set_exception_handler('Cubex\Cubex::exceptionHandler');

    /**
     * Define helpful bits :)
     */

    define("CUBEX_ENV", $env);
    define("CUBEX_WEB", isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']));
    define("CUBEX_CLI", isset($_SERVER['CUBEX_CLI']));
    define("WEB_ROOT", CUBEX_WEB ? $_SERVER['DOCUMENT_ROOT'] : false);
    $dirName = \dirname(__FILE__);
    define("CUBEX_ROOT", \substr(\dirname(__FILE__), -5) == 'cache' ? $dirName : \dirname($dirName));

    if(CUBEX_WEB && !isset($_REQUEST['__path__']))
    {
      Cubex::fatal("__path__ is not set. Your rewrite rules are not configured correctly.");
    }

    define("CUBEX_START", \microtime(true));

    $cubex = self::core(); //Construct Cubex

    Events::listen(Events::CUBEX_RESPONSE_PREPARE, array($cubex, 'responsePrepareHook'));

    $dispatcher = null;

    $request = new Request($_REQUEST['__path__']);
    Cubex::core()->setRequest($request);

    $response = new Response();
    $response->addHeader("X-Powered-By", "Cubex");
    $response->addHeader("X-Frame-Options", "deny");

    if(CUBEX_WEB)
    {
      list($verify, $dispatchPath) = \explode('/', \ltrim($_REQUEST['__path__'], '/'), 2);

      if(Cubex::config("dispatch")->getStr('base', 'res') == $verify)
      {
        $dispatcher = new Respond(
          Cubex::config("dispatch")->getArr("entity_map"),
          Cubex::config("dispatch")->getArr("domain_map"),
          $dispatchPath
        );
      }
      else
      {
        if(Cubex::config('locale')->getBool('enabled'))
        {
          Cubex::locale(Cubex::config('locale')->getStr('default', 'en_US'));
        }

        $loaderClass = Cubex::config("project")->getStr("dispatcher", '\Cubex\Applications\Loader');
        if(class_exists($loaderClass))
        {
          $dispatcher = new $loaderClass();
        }
        else
        {
          static::fatal("No Project Loader could be found");
        }
      }
    }
    else
    {
      Cubex::core();
    }

    if($dispatcher instanceof Dispatchable)
    {
      $respond = $dispatcher->dispatch($request, $response);
      if($respond instanceof Response)
      {
        Events::trigger(Events::CUBEX_RESPONSE_PREPARE, [], $respond);
        $respond->respond();
        Events::trigger(Events::CUBEX_RESPONSE_SENT, [], $respond);
      }
      else
      {
        throw new \RuntimeException("Invalid response from dispatcher");
      }
    }

    Events::trigger(Events::CUBEX_SHUTDOWN);
  }

  /**
   * Cubex singleton
   *
   * @return Cubex
   */
  public static function core()
  {
    if(self::$cubex === null)
    {
      self::$cubex = new Cubex();
      Events::trigger(Events::CUBEX_LAUNCH, [], self::$cubex);
    }

    return self::$cubex;
  }

  /**
   * Register auto loader and include cached file if exists
   *
   * @return Cubex
   */
  public static function register()
  {
    \set_include_path(\get_include_path() . PATH_SEPARATOR . CUBEX_ROOT);
    \spl_autoload_register("Cubex\\Cubex::loadClass");
    return self::$cubex;
  }

  /**
   * Load configuration and register autoloaders
   */
  private function __construct()
  {
    $this->configure();
    $this->appendClassMap(static::loadClassMap(__DIR__));
    $this->register();
  }

  /**
   * Define request object for applications to pull
   *
   * @param Http\Request $request
   *
   * @return \Cubex\Cubex
   */
  public function setRequest(Request $request)
  {
    $this->_request = $request;

    return $this;
  }

  /**
   * Globally available HTTP Request object
   *
   * @return Http\Request
   */
  public static function request()
  {
    return self::core()->_request;
  }

  /**
   * Define active controller object for views to pull
   *
   * @param \Cubex\Controller\BaseController $controller
   *
   * @return \Cubex\Cubex
   */
  public function setController(BaseController $controller)
  {
    $this->_controller = $controller;

    return $this;
  }

  /**
   * Globally available HTTP Request object
   *
   * @return \Cubex\Controller\BaseController
   */
  public static function controller()
  {
    return self::core()->_controller;
  }

  /**
   * Load environment configuration (ini)
   */
  private function configure()
  {
    try
    {
      $this->_configuration = \parse_ini_file(CUBEX_ROOT . '/conf/' . CUBEX_ENV . '.ini', true);
      if(isset($this->_configuration['general']['include_path']))
      {
        $applicationDir     = $this->_configuration['general']['include_path'];
        $this->_projectBase = \realpath($applicationDir);
        \set_include_path(\get_include_path() . PATH_SEPARATOR . $this->_projectBase);
      }
    }
    catch(\Exception $e)
    {
      self::fatal("Configuration file missing or invalid for '" . CUBEX_ENV . "' environment");
    }
  }

  /**
   * Database Connection
   *
   * @param string $connection
   *
   * @return \Cubex\Database\Connection
   */
  public static function db($connection = 'db')
  {
    return self::getConnection("database", $connection);
  }

  /**
   * Cache Connection
   *
   * @param string $connection
   *
   * @return \Cubex\Cache\Connection
   */
  public static function cache($connection = 'local')
  {
    return self::getConnection("cache", $connection);
  }

  /**
   * @param $type
   * @param $connection
   *
   * @return \Cubex\Data\Connection
   */
  private static function getConnection($type, $connection)
  {
    if(!isset(self::core()->_connections[$type][$connection]))
    {
      if(!isset(self::core()->_connections[$type]))
      {
        self::core()->_connections[$type] = array();
      }
      $config = self::config($type . "\\" . $connection);
      $layer  = "\\Cubex\\" . \ucwords($type) . "\\";
      $layer .= $config->getStr("engine", self::config($type)->getStr("engine", "mysql"));
      $layer .= "\\Connection";
      //Store connection
      self::core()->_connections[$type][$connection] = new $layer($config);
    }

    return self::core()->_connections[$type][$connection];
  }

  /**
   * Session Connection
   *
   * @return \Cubex\Session\Container
   */
  public static function session()
  {
    if(!isset(self::core()->_connections["session"]))
    {
      $layer = "\\Cubex\\Session\\";
      $layer .= self::config("session")->getStr("container", 'standard') . "\\Container";
      //Store Container
      self::core()->_connections["session"] = new $layer(self::config("session"));
    }

    return self::core()->_connections["session"];
  }

  /**
   * Get configuration object, within specific area
   *
   * @param $area
   *
   * @return Data\Handler
   */
  public static function config($area)
  {
    return new Handler(self::core()->_configuration[$area]);
  }

  /**
   * Entire environment configuration
   *
   * @return Data\Handler
   */
  public static function configuration()
  {
    return new Handler(self::core()->_configuration);
  }

  /**
   * Set locale and return Cubex or get locale
   *
   * @param null|string $locale
   *
   * @return Cubex|string
   */
  public static function locale($locale = null)
  {
    if($locale === null)
    {
      return self::core()->_locale;
    }
    $loc                  = \explode(',', $locale);
    self::core()->_locale = $loc[0];
    \putenv('LC_ALL=' . $loc[0]);
    \array_unshift($loc, LC_ALL);
    \call_user_func_array('setlocale', $loc);

    return self::$cubex;
  }

  /**
   * Include class file if not auto loaded
   *
   * @param $class
   */
  public static function loadClass($class)
  {
    $class = \ltrim($class, '\\');
    try
    {
      $includeFile = static::core()->getMappedClass($class);
      if($includeFile === null)
      {
        $class       = ltrim($class, '\\');
        $includeFile = '';
        if($lastNsPos = strrpos($class, '\\'))
        {
          $namespace   = substr($class, 0, $lastNsPos);
          $class       = substr($class, $lastNsPos + 1);
          $includeFile = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $includeFile .= strtolower(str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php');
      }

      include_once($includeFile);
    }
    catch(\Exception $e)
    {
    }
  }

  public function appendClassMap(array $classmap)
  {
    $this->_classmap = array_merge($this->_classmap, $classmap);
    return $this;
  }

  public function getMappedClass($class)
  {
    return isset($this->_classmap[$class]) ? $this->_classmap[$class] : null;
  }

  public static function loadClassMap($directory)
  {
    try
    {
      $map = parse_ini_file($directory . DIRECTORY_SEPARATOR . 'classmap.ini');
      foreach($map as $class => $location)
      {
        $map[$class] = $directory . DIRECTORY_SEPARATOR . $location;
      }
      return $map;
    }
    catch(\Exception $e)
    {
    }
    return [];
  }

  /**
   * Get full path to project
   *
   * @return string
   */
  public function projectBasePath()
  {
    return $this->_projectBase;
  }

  public static function setShutdownDetails($enabled = false)
  {
    self::core()->_allowShutdownDetails = (bool)$enabled;
  }

  public function responsePrepareHook(Event $e)
  {
    if(self::core()->_allowShutdownDetails)
    {
      $response = $e->getCallee();
      if($response instanceof Response)
      {
        $shutdownContent = "\nCompleted in: " . \number_format((\microtime(true) - CUBEX_START), 4) . " sec" .
        " - " . \number_format(((\microtime(true) - CUBEX_START)) * 1000, 1) . " ms";

        if(\defined('CUBEX_FATAL_ERROR'))
        {
          echo $shutdownContent;
          return;
        }

        $source = $response->getSource();

        switch($response->getRenderType())
        {
          case Response::RENDER_TEXT:
            $shutdownDebug = new HTMLElement('');
            $shutdownDebug->setContent($shutdownContent);
            $response->text($source . $shutdownContent);
            break;
          case Response::RENDER_WEBPAGE:
            $shutdownDebug = new HTMLElement(
              'div',
              array(
                   'id'    => 'cubex-shutdown-debug',
                   'style' => 'bottom:0; left:0; border:1px solid #666; border-left:0; border-bottom: 0;' .
                   'padding:3px; background:#FFFFFF; position:fixed;',
              ),
              $shutdownContent
            );
            if($source instanceof WebPage)
            {
              $source->closing .= $shutdownDebug;
            }
            break;
        }
      }
    }
  }

  /**
   * Shutdown handler
   */
  final public static function shutdown()
  {
    $event = \error_get_last();
    if(!$event || ($event['type'] != E_ERROR && $event['type'] != E_PARSE))
    {
      return;
    }

    $message = $event['message'] . "\n\n" . $event['file'] . ':' . $event['line'];

    self::fatal($message);
  }

  /**
   * Error handler
   *
   * @param       $code
   * @param       $message
   * @param       $file
   * @param       $line
   * @param       $context
   *
   * @throws \Exception
   */
  final public static function errorHandler($code, $message, $file, $line, $context)
  {
    switch($code)
    {
      case E_WARNING:
        throw new \Exception($message, $code);
        break;
      default:
        break;
    }
  }

  /**
   * Basic exception handler
   *
   * @param \Exception $e
   */
  final public static function exceptionHandler($e)
  {
    if($e instanceof \Exception)
    {
      Cubex::fatal(
        ($e->getCode() > 0 ? "[" . $e->getCode() . "] " : '')
        . $e->getMessage() . "\n" .
        "In: " . $e->getFile() . ':' . $e->getLine()
      );
    }
  }

  /**
   * Fatal message handler
   *
   * @param $message
   */
  final public static function fatal($message)
  {
    if(!\headers_sent())
    {
      \header("Content-Type: text/plain; charset=utf-8", true, 500);
    }
    echo "== Fatal Error ==\n";
    echo "Environment: " . (defined("CUBEX_ENV") ? CUBEX_ENV : 'Undefined') . "\n\n";
    echo $message . "\n";
    define("CUBEX_FATAL_ERROR", $message);
    exit(1);
  }
}
