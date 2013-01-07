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
use Cubex\Config\Config;
use Cubex\Dispatch\Fabricate;
use Cubex\Dispatch\Respond;
use Cubex\Event\Event;
use Cubex\Http\Request;
use Cubex\Event\Events;
use Cubex\ServiceManager\ServiceConfig;
use Cubex\ServiceManager\ServiceManager;
use Cubex\Http\Response;
use Cubex\Controller\BaseController;

/**
 * Cubex Framework Core
 */
final class Cubex
{
  public static $cubex = null;

  /**
   * @var \Cubex\ServiceManager\ServiceManager
   */
  protected $_serviceManager = null;

  private $_request = null;
  /**
   * @var array
   */
  private $_configuration = null;
  private $_projectBase = '';
  private $_allowShutdownDetails = true;
  private $_classmap = array();

  public static function canBoot()
  {
    //Do not check version if running through a compiler
    if(\defined('PHP_MAJOR_VERSION'))
    {
      $requiredVersion = '5.4.0';
      $currentVersion  = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
      if($currentVersion < $requiredVersion)
      {
        Cubex::fatal(
          "You are running PHP '" . $currentVersion . "', version '{$requiredVersion}' required"
        );
      }
    }
  }

  public static function setupEnv()
  {
    $env = \getenv('CUBEX_ENV'); // Apache Config
    if(!$env && isset($_ENV['CUBEX_ENV']))
    {
      $env = $_ENV['CUBEX_ENV'];
    }
    if(!$env)
    {
      Cubex::fatal("The 'CUBEX_ENV' environmental variable is not defined.");
    }

    define("CUBEX_ENV", $env);
  }

  /**
   * Generate a transaction specific ID
   *
   * @return string Transaction ID
   */
  public static function createTransaction()
  {
    $host = explode('.', php_uname('n'));
    if(count($host) > 2)
    {
      array_pop($host);
      array_pop($host);
    }

    $hash = md5(serialize($_SERVER));

    return substr(md5(implode('.', $host)), 0, 10) . time() . substr(
      $hash, 0, 8
    );
  }

  /**
   * Verify and setup the environment for cubex to run in
   */
  final public static function boot()
  {
    define("CUBEX_START", \microtime(true));

    static::canBoot();
    static::setupEnv();
    define("CUBEX_TRANSACTION", static::createTransaction());

    \register_shutdown_function('Cubex\Cubex::shutdown');
    \set_error_handler('Cubex\Cubex::errorHandler');
    \set_exception_handler('Cubex\Cubex::exceptionHandler');

    define("CUBEX_WEB", isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']));
    define("CUBEX_CLI", isset($_SERVER['CUBEX_CLI']));
    define("WEB_ROOT", CUBEX_WEB ? $_SERVER['DOCUMENT_ROOT'] : false);
    if(!defined('CUBEX_ROOT'))
    {
      define("CUBEX_ROOT", dirname(dirname(__FILE__)));
    }

    if(CUBEX_WEB && !isset($_REQUEST['__path__']))
    {
      Cubex::fatal(
        "__path__ is not set. Your rewrite rules are not configured correctly."
      );
    }

    $cubex = self::core(); //Construct Cubex
    Events::trigger(Events::CUBEX_LAUNCH, [], $cubex);
    Events::listen(
      Events::CUBEX_RESPONSE_PREPARE, array($cubex, 'responseDebugInfo')
    );

    $cubex->_serviceManager = new ServiceManager();
    $dispatcher             = null;
    $request                = new Request($_REQUEST['__path__']);
    Cubex::core()->setRequest($request);
    self::configureServiceManager(
      $cubex->_serviceManager, $cubex->configuration()
    );

    $response = new Response();
    $response->addHeader("X-Cubex-TID", CUBEX_TRANSACTION);
    $response->addHeader("X-Frame-Options", "deny");

    if(self::config('response')->getBool('gzip', true))
    {
      ini_set('zlib.output_compression', 'On');
    }

    if($_SERVER['REQUEST_URI'] == '/favicon.ico')
    {
      $domainHash = Fabricate::generateDomainHash(
        $request->getSubDomain() . '.' . $request->getDomain() . $request->getTld()
      );
      $dispatcher = new Respond([], [], $domainHash . '/esabot/pamon/favicon.ico');
    }
    else if(CUBEX_WEB)
    {
      list($verify, $dispatchPath) = \explode(
        '/', \ltrim($_REQUEST['__path__'], '/'), 2
      );

      if(Cubex::config("dispatch")->getStr('base', 'res') == $verify)
      {
        $dispatcher = new \Cubex\Dispatch\Respond(
          Cubex::config("dispatch")->getArr("entity_map"),
          Cubex::config("dispatch")->getArr("domain_map"),
          $dispatchPath
        );
      }
      else
      {
        $loaderClass = Cubex::config("project")->getStr(
          "dispatcher", '\Cubex\Applications\Loader'
        );
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

    if($dispatcher instanceof Dispatchable)
    {
      $respond = $dispatcher->dispatch($request, $response);
      if($respond instanceof Response)
      {
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
   * @param \Cubex\ServiceManager\ServiceManager $sm
   * @param \Cubex\Config\Config                 $config
   *
   * @return \Cubex\ServiceManager\ServiceManager
   */
  public function configureServiceManager(ServiceManager $sm, Config $config)
  {
    $localeServiceConfig = new ServiceConfig();
    $localeServiceConfig->setFactory(new \Cubex\Locale\Factory());
    $localeServiceConfig->fromConfig(new Config($config->getArr('locale')));
    $sm->register("locale", $localeServiceConfig, true);

    foreach($config as $section => $conf)
    {
      if(stristr($section, '\\'))
      {
        $parent = current(explode('\\', $section));
        $conf   = array_merge($config->getArr($parent, []), $conf);
      }

      if(isset($conf['factory']) && isset($conf['register_service_as']))
      {
        $service = new ServiceConfig();
        $service->fromConfig(new Config($conf));
        $shared = isset($conf['register_service_shared']) ? (bool)$conf['register_service_shared'] : true;
        $sm->register($conf['register_service_as'], $service, $shared);
      }
    }

    return $sm;
  }

  /**
   * @return ServiceManager
   */
  public function getServiceManager()
  {
    return $this->_serviceManager;
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
    $this->_configure();
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
   * Load environment configuration (ini)
   */
  private function _configure()
  {
    try
    {
      $this->_configuration = \parse_ini_file(
        CUBEX_ROOT . '/conf/' . CUBEX_ENV . '.ini', true
      );
      if(isset($this->_configuration['general']['include_path']))
      {
        $applicationDir = $this->_configuration['general']['include_path'];
        $dirs           = explode(PATH_SEPARATOR, $applicationDir);
        foreach($dirs as $i => $dir)
        {
          $dir = \realpath($dir);
          if($i == 0)
          {
            $this->_projectBase = $dir;
          }
          \set_include_path(\get_include_path() . PATH_SEPARATOR . $dir);
        }
      }
    }
    catch(\Exception $e)
    {
      self::fatal(
        "Configuration file missing or invalid for '" . CUBEX_ENV . "' environment"
      );
    }
  }

  /**
   * Get configuration object, within specific area
   *
   * @param $area
   *
   * @return Config
   */
  public static function config($area)
  {
    return new Config(self::core()->_configuration[$area]);
  }

  /**
   * Entire environment configuration
   *
   * @return Config
   */
  public static function configuration()
  {
    return new Config(self::core()->_configuration);
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
          $includeFile = strtolower(
            str_replace(
              '\\', DIRECTORY_SEPARATOR, $namespace
            ) . DIRECTORY_SEPARATOR
          );
        }
        $includeFile .= strtolower(
          str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php'
        );
      }
      include_once $includeFile;
    }
    catch(\Exception $e)
    {
    }
  }

  /**
   * @param array $classmap
   *
   * @return $this
   */
  public function appendClassMap(array $classmap)
  {
    $this->_classmap = array_merge($this->_classmap, $classmap);
    return $this;
  }

  /**
   * @param $class
   *
   * @return null
   */
  public function getMappedClass($class)
  {
    return isset($this->_classmap[$class]) ? $this->_classmap[$class] : null;
  }

  /**
   * @param $directory
   *
   * @return array
   */
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
      return [];
    }
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

  /**
   * @param bool $enabled
   */
  public static function setShutdownDetails($enabled = false)
  {
    self::core()->_allowShutdownDetails = (bool)$enabled;
  }

  /**
   * @param \Cubex\Event\Event $e
   */
  public function responseDebugInfo(Event $e)
  {
    if($this->_allowShutdownDetails)
    {
      $response = $e->getCallee();
      if($response instanceof Response)
      {
        $shutdownContent = "Completed in: " . \number_format(
          (\microtime(true) - CUBEX_START), 4
        ) . " sec" .
        " - " . \number_format(
          ((\microtime(true) - CUBEX_START)) * 1000, 1
        ) . " ms" .
        "\nTransaction: " . (defined(
          "CUBEX_TRANSACTION"
        ) ? CUBEX_TRANSACTION : 'UNKNOWN');

        if(\defined('CUBEX_FATAL_ERROR'))
        {
          echo $shutdownContent;
          return;
        }

        $source = $response->getSource();

        switch($response->getRenderType())
        {
          case Response::RENDER_TEXT:
            $shutdownDebug = new \Cubex\View\HTMLElement('');
            $shutdownDebug->setContent($shutdownContent);
            $response->text($source . "\n" . $shutdownContent);
            break;
          case Response::RENDER_WEBPAGE:
            $shutdownDebug = new \Cubex\View\HTMLElement(
              'div',
              array(
                   'id'    => 'cubex-shutdown-debug',
                   'style' => 'bottom:0; left:0; border:1px solid #666; border-left:0; border-bottom: 0;' .
                   'padding:3px; background:#FFFFFF; position:fixed;',
              ),
              nl2br($shutdownContent)
            );
            if($source instanceof \Cubex\Response\WebPage)
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
  final public static function errorHandler($code, $message, $file, $line,
                                            $context)
  {
    switch($code)
    {
      case E_WARNING:
        throw new \Exception(
          "$message\nFile: $file\nLine:$line\nContext:" . json_encode($context),
          $code
        );
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
    echo "Environment: " . (defined(
      "CUBEX_ENV"
    ) ? CUBEX_ENV : 'Undefined') . "\n\n";
    echo $message . "\n";
    define("CUBEX_FATAL_ERROR", $message);
    exit(1);
  }
}
