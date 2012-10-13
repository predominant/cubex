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
    echo "Completed in: " . number_format((microtime(true) - CUBEX_START) * 1000, 3) . " ms";

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

define("CUBEX_START", microtime(true));

class Cubex
{

  public static $cubex = null;

  private $_path = null;
  private $_configuration = null;

  public static function Core($path = null)
  {
    if(self::$cubex === null) self::$cubex = new Cubex($path);

    return self::$cubex;
  }

  public function __construct($path = null)
  {
    if($path !== null) $this->_path = $path;
    $this->configure();
    spl_autoload_register(array($this, 'LoadClass'), true, true);
  }

  private function configure()
  {
    try
    {
      $this->_configuration = parse_ini_file(CUBEX_ROOT . '/conf/' . CUBEX_ENV . '.ini', true);
    }
    catch(\Exception $e)
    {
      fatal("Configuration file missing for '" . CUBEX_ENV . "' environment");
    }
  }

  public static function Config($area, $item = null)
  {
    if($item === null) return self::Core()->_configuration[$area];
    else return self::Core()->_configuration[$area][$item];
  }

  public function Debug()
  {
    $output   = array();
    $output[] = "Environment:\t" . $this->Config('environment');
    $output[] = "CUBEX_ENV:\t" . CUBEX_ENV;
    $output[] = "CUBEX_WEB:\t" . (CUBEX_WEB ? 'True' : 'False');
    if(CUBEX_WEB) $output[] = "WEB_ROOT:\t" . WEB_ROOT;
    $output[] = "CUBEX_ROOT:\t" . CUBEX_ROOT;

    $msg = CUBEX_WEB ? '<pre>' : '';
    $msg .= implode("\n", $output);
    $msg .= CUBEX_WEB ? '</pre>' : '';

    return $msg;
  }

  public function LoadClass($class)
  {
    include_once(CUBEX_ROOT . '/cubes/' . str_replace('_', '/', $class) . '.php');
  }
}

echo Cubex::Core($_REQUEST['__path__'])->Debug();
