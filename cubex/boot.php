<?php
/**
 * User: brooke.bryan
 * Date: 13/10/12
 * Time: 18:33
 * Description: Bootstrap
 */
namespace Cubex;

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

define("CUBEX_ENV", $env);
define("CUBEX_WEB", isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']));
define("WEB_ROOT", CUBEX_WEB ? $_SERVER['DOCUMENT_ROOT'] : false);
define("CUBEX_ROOT", dirname(dirname(__FILE__)));

if(CUBEX_WEB && !isset($_REQUEST['__path__']))
{
  Cubex::fatal("__path__ is not set. Your rewrite rules are not configured correctly.");
}

/* Translation functions */

function t($string)
{
  return _($string);
}

function p($singular, $plural = null, $number = 0)
{
  return ngettext($singular, $plural, $number);
}

function dt($domain, $string)
{
  return dgettext($domain, $string);
}

function dp($domain, $singular, $plural = null, $number = 0)
{
  return dngettext($domain, $singular, $plural, $number);
}

function btdom($domain)
{
  return bindtextdomain($domain, CUBEX_ROOT . "/locale");
}

function tdom($domain, $bind = false)
{
  if($bind) btdom($domain);

  return textdomain($domain);
}

/* Translation functions */

define("CUBEX_START", microtime(true));

class Cubex
{

  public static $cubex = null;

  private $_request = null;
  private $_configuration = null;
  private $_connections = null;
  private $_locale = null;

  public static function core()
  {
    if(self::$cubex === null) self::$cubex = new Cubex();

    return self::$cubex;
  }

  public static function register()
  {
    spl_autoload_register("Cubex\\Cubex::loadClass");

    if(!class_exists("Core", false))
    {
      try
      {
        $cached = CUBEX_ROOT . DIRECTORY_SEPARATOR . 'cubex' . DIRECTORY_SEPARATOR;
        $cached .= 'cache' . DIRECTORY_SEPARATOR . 'core.php';
        include_once($cached);
      }
      catch(\Exception $e)
      {
      }
    }

    return self::$cubex;
  }

  public function __construct()
  {
    $this->configure();
    $this->register();
  }

  public function setRequest(Http\Request $request)
  {
    $this->_request = $request;
  }

  public static function request()
  {
    return self::core()->_request;
  }

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
   *
   * @param string $connection
   * @return \Cubex\Database\Connection
   */
  public static function db($connection = 'db')
  {
    return self::getConnection("database", $connection);
  }

  /**
   * @param string $connection
   * @return \Cubex\Cache\Connection
   */
  public static function cache($connection = 'local')
  {
    return self::getConnection("cache", $connection);
  }

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

  public static function config($area)
  {
    return new \Cubex\Data\Handler(self::core()->_configuration[$area]);
  }

  public static function configuration()
  {
    return new \Cubex\Data\Handler(self::core()->_configuration);
  }

  public static function locale($locale = null)
  {
    if($locale === null) return self::core()->_locale;
    self::core()->_locale = $locale;
    putenv('LC_ALL=' . $locale);

    return self::$cubex;
  }

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
      include_once(CUBEX_ROOT . DIRECTORY_SEPARATOR . strtolower(str_replace('_', '/', $class)) . '.php');
    }
    catch(\Exception $e)
    {
    }
  }


  final public static function shutdown()
  {
    echo "\n<br/>Completed in: " . number_format((microtime(true) - CUBEX_START) * 1000, 3) . " ms";
    $event = error_get_last();

    if(!$event || ($event['type'] != E_ERROR && $event['type'] != E_PARSE))
    {
      return;
    }

    $message = $event['message'] . "\n\n" . $event['file'] . ':' . $event['line'];

    self::fatal($message);
  }

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

  final public static function fatal($message)
  {
    header("Content-Type: text/plain; charset=utf-8", true, 500);
    echo "== Fatal Error ==\n\n";
    echo $message . "\n";
    exit(1);
  }
}

Cubex::core()->setRequest(new Http\Request($_REQUEST['__path__']));
if(Cubex::config('locale')->getBool('enabled'))
{
  Cubex::locale(Cubex::config('locale')->getStr('default', 'en_US'));
}

Application\Loader::load(Cubex::request());
