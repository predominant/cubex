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
use Cubex\Base\Controller;
use Cubex\Data\Handler;
use Cubex\Http\Request;
use Cubex\Events\Events;
use Cubex\View\HTMLElement;

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

  /**
   * Verify and setup the environment for cubex to run in
   */
  final public static function boot()
  {
    if(\defined('PHP_MAJOR_VERSION')) //Do not check version if running through a compiler
    {
      $required_version = '5.4.0';
      $current_version  = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
      if($current_version < $required_version)
      {
        Cubex::fatal("You are running PHP '" . $current_version . "', version '{$required_version}' required");
      }
    }

    $env = \getenv('CUBEX_ENV'); // Apache Config
    if(!$env && isset($_ENV['CUBEX_ENV'])) $env = $_ENV['CUBEX_ENV'];
    if(!$env) Cubex::fatal("The 'CUBEX_ENV' environmental variable is not defined.");

    \register_shutdown_function('Cubex\Cubex::shutdown');
    \set_error_handler('Cubex\Cubex::error_handler');
    \set_exception_handler('Cubex\Cubex::exception_handler');

    /**
     * Define helpful bits :)
     */

    define("CUBEX_ENV", $env);
    define("CUBEX_WEB", isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']));
    define("WEB_ROOT", CUBEX_WEB ? $_SERVER['DOCUMENT_ROOT'] : false);
    $dirname = \dirname(\dirname(__FILE__));
    define("CUBEX_ROOT", \substr(\dirname(__FILE__), -5) == 'cache' ? $dirname : \dirname($dirname));

    if(CUBEX_WEB && !isset($_REQUEST['__path__']))
    {
      Cubex::fatal("__path__ is not set. Your rewrite rules are not configured correctly.");
    }

    define("CUBEX_START", \microtime(true));

    self::core(); //Construct Cubex

    if(CUBEX_WEB)
    {
      Cubex::core()->setRequest(new Request($_REQUEST['__path__']));
      if(Cubex::config('locale')->getBool('enabled'))
      {
        Cubex::locale(Cubex::config('locale')->getStr('default', 'en_US'));
      }

      \Cubex\Applications\Loader::load(Cubex::request());
    }
    else
    {
      Cubex::core();
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
    if(self::$cubex === null) self::$cubex = new Cubex();

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

    if(!class_exists("Core", false))
    {
      try
      {
        $cached = CUBEX_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'core.php';
        include_once($cached);
      }
      catch(\Exception $e)
      {
      }
    }

    return self::$cubex;
  }

  /**
   * Load configuration and register autoloaders
   */
  private function __construct()
  {
    $this->configure();
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
   * @param \Cubex\Base\Controller $controller
   *
   * @return \Cubex\Cubex
   */
  public function setController(Controller $controller)
  {
    $this->_controller = $controller;

    return $this;
  }

  /**
   * Globally available HTTP Request object
   *
   * @return \Cubex\Base\Controller
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
        $application_dir = $this->_configuration['general']['include_path'];
        \set_include_path(\get_include_path() . PATH_SEPARATOR . \realpath($application_dir));
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
      if(!isset(self::core()->_connections[$type])) self::core()->_connections[$type] = array();
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
    if($locale === null) return self::core()->_locale;
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
      if(\strpos($class, 'Cubex\\') === 0)
      {
        $class = \substr($class, 6);
      }

      if(\strpos($class, 'Modules\\') === 0)
      {
        //Add some module loaders to handle more complex modules
      }
      else if(\strpos($class, 'Widgets\\') === 0)
      {
        //Add some widget loaders
      }
      else if(\strpos($class, 'Applications\\') === 0 || \strpos($class, 'Components\\') === 0)
      {
        $parts = \explode('\\', $class);

        if(\count($parts) > 2)
        {
          $end = $parts[\count($parts) - 1];

          if(\substr($class, -10) === 'Controller')
          {
            $end                       = 'controllers\\' . \substr($end, 0, -10);
            $parts[\count($parts) - 1] = $end;
            $class                     = \implode('\\', $parts);
          }
          else if($end === 'Constants')
          {
            $class .= '\\Constants';
          }
          else if(\substr($class, -9) === 'Constants')
          {
            $end                       = 'constants\\' . \substr($end, 0, -9);
            $parts[\count($parts) - 1] = $end;
            $class                     = \implode('\\', $parts);
          }
        }
      }
      else
      {
        $class = 'cubes' . DIRECTORY_SEPARATOR . $class;
      }

      $include_file = \strtolower(\str_replace('_', '/', \str_replace('\\', '/', $class))) . '.php';
      include_once($include_file);
    }
    catch(\Exception $e)
    {
    }
  }


  /**
   * Shutdown handler
   */
  final public static function shutdown()
  {
    $fatal = \defined('CUBEX_FATAL_ERROR');
    if(CUBEX_WEB && !$fatal)
    {
      $shutdown_debug = new HTMLElement(
        'div', '',
        array(
             'id'    => 'cubex-shutdown-debug',
             'style' => 'bottom:0; left:0; border:1px solid #666; border-left:0; border-bottom: 0;' .
             'padding:3px; background:#FFFFFF; position:fixed;',
        )
      );
    }
    else
    {
      $shutdown_debug = new HTMLElement("");
    }

    echo $shutdown_debug->setContent(
      "Completed in: " . \number_format((\microtime(true) - CUBEX_START), 4) . " sec" .
      " - " . \number_format(((\microtime(true) - CUBEX_START)) * 1000, 1) . " ms"
    );

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
  final public static function error_handler($code, $message, $file, $line, $context)
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
   * @param \Exception $e
   */
  final public static function exception_handler($e)
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
    echo "== Fatal Error ==\n\n";
    echo $message . "\n";
    define("CUBEX_FATAL_ERROR", $message);
    exit(1);
  }
}
