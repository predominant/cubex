<?php
/**
 * User: brooke.bryan
 * Date: 13/10/12
 * Time: 18:33
 * Description: Bootstrap
 */
namespace Cubex;

error_reporting(E_ALL);
ini_set('display_errors', true);

$required_version = '5.4.0';
if(version_compare(PHP_VERSION, $required_version) < 0)
{
  fatal("You are running PHP '" . PHP_VERSION . "', version '{$required_version}' required");
}

$env = getenv('CUBEX_ENV'); // Apache Config
if(!$env && isset($_ENV['CUBEX_ENV'])) $env = $_ENV['CUBEX_ENV'];
if(!$env) fatal("The 'CUBEX_ENV' environmental variable is not defined.");

register_shutdown_function('Cubex\shutdown');
set_error_handler('Cubex\error_handler');

define("CUBEX_ENV", $env);
define("CUBEX_WEB", isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']));
define("WEB_ROOT", CUBEX_WEB ? $_SERVER['DOCUMENT_ROOT'] : false);
define("CUBEX_ROOT", dirname(dirname(__FILE__)));

if(CUBEX_WEB && !isset($_REQUEST['__path__']))
{
  fatal("__path__ is not set. Your rewrite rules are not configured correctly.");
}

function shutdown()
{
  $event = error_get_last();

  if(!$event || ($event['type'] != E_ERROR && $event['type'] != E_PARSE))
  {
    echo "\n<br/>Completed in: " . number_format((microtime(true) - CUBEX_START) * 1000, 3) . " ms";

    return;
  }

  $message = $event['message'] . "\n\n" . $event['file'] . ':' . $event['line'];

  fatal($message);
}

function error_handler($code, $message, $file, $line, array $context)
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

function fatal($message)
{
  header("Content-Type: text/plain; charset=utf-8", true, 500);
  echo "== Fatal Error ==\n\n";
  echo $message . "\n";
  exit(1);
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

/* Translation functions */

define("CUBEX_START", microtime(true));

class Cubex
{

  public static $cubex = null;

  private $_path = null;
  private $_configuration = null;
  private $_connections = null;
  private $_locale = null;

  public static function Core($path = null)
  {
    if(self::$cubex === null)
    {
      self::$cubex = new Cubex($path);
    }

    return self::$cubex;
  }

  public static function Register()
  {
    spl_autoload_register("Cubex\Cubex::LoadClass");

    return self::$cubex;
  }

  public function __construct($path = null)
  {
    if($path !== null) $this->_path = $path;
    $this->configure();
    $this->Register();
  }

  public static function Path()
  {
    return self::Core()->_path;
  }

  private function configure()
  {
    try
    {
      $this->_configuration = parse_ini_file(CUBEX_ROOT . '/conf/' . CUBEX_ENV . '.ini', true);
    }
    catch(\Exception $e)
    {
      fatal("Configuration file missing or invalid for '" . CUBEX_ENV . "' environment");
    }
  }

  /**
   * @param string $connection
   * @return \Database\Connection
   */
  public static function DB($connection = 'db')
  {
    return self::GetConnection("database", $connection);
  }

  /**
   * @param string $connection
   * @return \Cache\Connection
   */
  public static function Cache($connection = 'local')
  {
    return self::GetConnection("cache", $connection);
  }

  private static function GetConnection($type, $connection)
  {
    if(!isset(self::Core()->_connections[$type][$connection]))
    {
      if(!isset(self::Core()->_connections[$type])) self::Core()->_connections[$type] = array();
      $config = self::config($type . "\\" . $connection);
      $layer  = "\\" . ucwords($type) . "\\";
      $layer .= C::ArrayValue($config, 'engine', self::Config($type, "engine"));
      $layer .= "\Connection";
      //Store connection
      self::Core()->_connections[$type][$connection] = new $layer($config);
    }

    return self::Core()->_connections[$type][$connection];
  }

  /**
   * @return \Session\Container
   */
  public static function Session()
  {
    if(!isset(self::Core()->_connections["session"]))
    {
      $layer = "\Session\\" . self::Config("session", "container") . "\Container";
      //Store Container
      self::Core()->_connections["session"] = new $layer(self::config("session"));
    }

    return self::Core()->_connections["session"];
  }

  public static function Config($area, $item = null)
  {
    if($item === null) return self::Core()->_configuration[$area];
    else return self::Core()->_configuration[$area][$item];
  }

  public static function Locale($locale = null)
  {
    if($locale === null) return self::Core()->_locale;
    self::Core()->_locale = $locale;
    putenv('LC_ALL=' . $locale);

    return self::$cubex;
  }

  public static function LoadClass($class)
  {
    include_once(CUBEX_ROOT . '/cubes/' . str_replace('_', '/', $class) . '.php');
  }
}

Cubex::Core($_REQUEST['__path__']);

if(Cubex::Config('locale', 'enabled'))
{
  Cubex::Locale(C::ArrayValue($_REQUEST, 'locale', Cubex::Config("locale", "default")));
}

//Basic Translations
/*
bindtextdomain("messages", CUBEX_ROOT . "/locale");
textdomain("messages");

$n = rand(0, 10);
printf(p("There is %d comment", "There are %d comments", $n), $n);
echo " : " . t("Hello world") . " " . t("Color");
*/
