<?php
/**
 * User: brooke.bryan
 * Date: 13/10/12
 * Time: 18:33
 * Description: Bootstrap Cubex
 */
namespace Cubex;

if(function_exists('xhprof_enable'))
{
  xhprof_enable(XHPROF_FLAGS_NO_BUILTINS);
}

$required_version = '5.4.0';
if(version_compare(PHP_VERSION, $required_version) < 0)
{
  Cubex::fatal("You are running PHP '" . PHP_VERSION . "', version '{$required_version}' required");
}

$env = getenv('CUBEX_ENV'); // Apache Config
if(!$env && isset($_ENV['CUBEX_ENV'])) $env = $_ENV['CUBEX_ENV'];
if(!$env) Cubex::fatal("The 'CUBEX_ENV' environmental variable is not defined.");

register_shutdown_function('Cubex\Cubex::shutdown');
set_error_handler('Cubex\Cubex::error_handler');

/*
 * Define helpful bits :)
 */

define("CUBEX_ENV", $env);
define("CUBEX_WEB", isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']));
define("WEB_ROOT", CUBEX_WEB ? $_SERVER['DOCUMENT_ROOT'] : false);
define("CUBEX_ROOT",
substr(dirname(__FILE__), -5) == 'cache' ? dirname(dirname(__FILE__)) : dirname(dirname(dirname(__FILE__)))
);

if(CUBEX_WEB && !isset($_REQUEST['__path__']))
{
  Cubex::fatal("__path__ is not set. Your rewrite rules are not configured correctly.");
}

define("CUBEX_START", microtime(true));

/* Translation functions */

/**
 * Translate string to locale, wrapper for gettext
 *
 * @link http://php.net/manual/en/function.gettext.php
 * @param string $string
 * @return string
 */
function t($string)
{
  return _($string);
}

/**
 * Translate plural, wrapper for ngettext
 *
 * @link http://php.net/manual/en/function.ngettext.php
 * @param      $singular
 * @param null $plural
 * @param int  $number
 * @return string
 */
function p($singular, $plural = null, $number = 0)
{
  return ngettext($singular, $plural, $number);
}

/**
 * Translate string using specific domain
 *
 * @link http://php.net/manual/en/function.dgettext.php
 * @param $domain
 * @param $string
 * @return string
 */
function dt($domain, $string)
{
  return dgettext($domain, $string);
}

/**
 * Translate plural, using specific domain
 *
 * @link http://php.net/manual/en/function.dngettext.php
 * @param      $domain
 * @param      $singular
 * @param null $plural
 * @param int  $number
 * @return string
 */
function dp($domain, $singular, $plural = null, $number = 0)
{
  return dngettext($domain, $singular, $plural, $number);
}

/**
 * Bind domain to language file (CUBEX_ROOT/locale)
 *
 * @link http://php.net/manual/en/function.bindtextdomain.php
 * @param $domain
 * @return string
 */
function btdom($domain)
{
  return bindtextdomain($domain, CUBEX_ROOT . "/locale");
}

/**
 * Set the text domain, wrapper for textdomain
 *
 * @link http://php.net/manual/en/function.textdomain.php
 * @param string     $domain
 * @param bool       $bind
 * @return string
 */
function tdom($domain, $bind = false)
{
  if($bind) btdom($domain);

  return textdomain($domain);
}

/**
 * Cubex Framework
 */
class Cubex
{

  public static $cubex = null;

  private $_request = null;
  private $_configuration = null;
  private $_connections = null;
  private $_locale = null;

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
    spl_autoload_register("Cubex\\Cubex::loadClass");

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
  public function __construct()
  {
    $this->configure();
    $this->register();
  }

  /**
   * Define request object for applications to pull
   *
   * @param Http\Request $request
   * @return \Cubex\Cubex
   */
  public function setRequest(Http\Request $request)
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
   * Load environment configuration (ini)
   */
  private function configure()
  {
    try
    {
      $this->_configuration = parse_ini_file(CUBEX_ROOT . '/conf/' . CUBEX_ENV . '.ini', true);
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
   * @return \Cubex\Cache\Connection
   */
  public static function cache($connection = 'local')
  {
    return self::getConnection("cache", $connection);
  }

  /**
   * @param $type
   * @param $connection
   * @return mixed
   */
  private static function getConnection($type, $connection)
  {
    if(!isset(self::core()->_connections[$type][$connection]))
    {
      if(!isset(self::core()->_connections[$type])) self::core()->_connections[$type] = array();
      $config = self::config($type . "\\" . $connection);
      $layer  = "\\Cubex\\" . ucwords($type) . "\\";
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
   * @return Data\Handler
   */
  public static function config($area)
  {
    return new \Cubex\Data\Handler(self::core()->_configuration[$area]);
  }

  /**
   * Entire environment configuration
   *
   * @return Data\Handler
   */
  public static function configuration()
  {
    return new \Cubex\Data\Handler(self::core()->_configuration);
  }

  /**
   * Set locale and return Cubex or get locale
   *
   * @param null|string $locale
   * @return Cubex|string
   */
  public static function locale($locale = null)
  {
    if($locale === null) return self::core()->_locale;
    self::core()->_locale = $locale;
    putenv('LC_ALL=' . $locale);

    return self::$cubex;
  }

  /**
   * Include class file if not auto loaded
   *
   * @param $class
   */
  public static function loadClass($class)
  {
    try
    {
      if(strpos($class, 'Cubex\\') === 0)
      {
        $class = substr($class, 6);
      }

      if(!(strpos($class, 'Application\\') === 0))
      {
        $class = 'cubes' . DIRECTORY_SEPARATOR . $class;
      }
      else
      {
        $parts = explode('\\', $class);

        if(count($parts) > 2)
        {
          $end = $parts[count($parts) - 1];

          if(substr($class, -10) === 'Controller')
          {
            $end                      = 'controller\\' . substr($end, 0, -10);
            $parts[count($parts) - 1] = $end;
            $class                    = implode('\\', $parts);
          }
          else if($end === 'Events')
          {
            $class .= '\\Events';
          }
          else if($end === 'Constants')
          {
            $class .= '\\Constants';
          }
          else if(substr($class, -6) === 'Events')
          {
            $end                      = 'events\\' . substr($end, 0, -6);
            $parts[count($parts) - 1] = $end;
            $class                    = implode('\\', $parts);
          }
          else if(substr($class, -9) === 'Constants')
          {
            $end                      = 'constants\\' . substr($end, 0, -9);
            $parts[count($parts) - 1] = $end;
            $class                    = implode('\\', $parts);
          }
          else if($parts[2] !== 'Application')
          {
            $parts[2] = 'lib\\' . $parts[2];
            $class    = implode('\\', $parts);
          }
        }
      }

      include_once(
      CUBEX_ROOT . DIRECTORY_SEPARATOR
      . strtolower(str_replace('_', '/', str_replace('\\', '/', $class)))
      . '.php'
      );
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
    echo CUBEX_WEB ? '<br/>' : "\n";
    echo "Completed in: " . number_format((microtime(true) - CUBEX_START) * 1000, 3) . " ms";
    $event = error_get_last();

    if(self::core()->config('general')->getBool("debug", false) && function_exists("xhprof_disable"))
    {
      $xhprof_data = xhprof_disable();
      $XHPROF_ROOT = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/facebook/xhprof';
      include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
      include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

      $xhprof_runs = new \XHProfRuns_Default();
      $run_id      = $xhprof_runs->save_run($xhprof_data, "xhprof_cubex");

      echo '<br/><a target="_blank" href="http://www.xhprof.local/index.php?run=' . $run_id;
      echo '&source=xhprof_cubex">Debug Data</a>';
    }

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
   * @param array $context
   * @throws \Exception
   */
  final public static function error_handler($code, $message, $file, $line, array $context)
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
   * Fatal message handler
   *
   * @param $message
   */
  final public static function fatal($message)
  {
    header("Content-Type: text/plain; charset=utf-8", true, 500);
    echo "== Fatal Error ==\n\n";
    echo $message . "\n";
    exit(1);
  }
}

if(CUBEX_WEB)
{
  Cubex::core()->setRequest(new Http\Request($_REQUEST['__path__']));
  if(Cubex::config('locale')->getBool('enabled'))
  {
    Cubex::locale(Cubex::config('locale')->getStr('default', 'en_US'));
  }

  try
  {
    Application\Loader::load(Cubex::request());
  }
  catch(\Exception $e)
  {
    Cubex::fatal($e->getMessage());
  }
}
else
{
  Cubex::core();
}
