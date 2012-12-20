<?php
/**
 * User: brooke.bryan
 * Date: 13/12/12
 * Time: 19:35
 * Description:
 */
namespace Cubex\Logger;

use Cubex\Event\Events;

/**
 * Base logger class (Triggers Cubex Log Events)
 */
class Log
{

  const LEVEL_INFO       = 'info';
  const LEVEL_SUCCESS    = 'success';
  const LEVEL_WARNING    = 'warning';
  const LEVEL_CRITICAL   = 'critical';
  const LEVEL_FATAL      = 'fatal';
  const LEVEL_PARSE      = 'parse';
  const LEVEL_DEPRECATED = 'deprecated';

  const TYPE_GENERIC     = 'generic';
  const TYPE_DEBUG       = 'debug';
  const TYPE_APPLICATION = 'application';
  const TYPE_CONTROLLER  = 'controller';
  const TYPE_WIDGET      = 'widget';
  const TYPE_MODULE      = 'module';
  const TYPE_QUERY       = 'query';
  const TYPE_API         = 'api';

  /**
   * Informative Log
   *
   * @param        $message
   * @param        $code
   * @param string $type
   */
  public static function info($message, $code, $type = self::TYPE_GENERIC)
  {
    static::_log(self::LEVEL_INFO, $message, $type, $code);
  }

  /**
   * Log a successful thingy
   *
   * @param        $message
   * @param        $code
   * @param string $type
   */
  public static function success($message, $code, $type = self::TYPE_GENERIC)
  {
    static::_log(self::LEVEL_SUCCESS, $message, $type, $code);
  }

  /**
   * Oh dear, someone should be warned about this
   *
   * @param        $message
   * @param        $code
   * @param string $type
   */
  public static function warning($message, $code, $type = self::TYPE_GENERIC)
  {
    static::_log(self::LEVEL_WARNING, $message, $type, $code);
  }

  /**
   * That really shouldn't have happened, but, I guess it did
   *
   * @param        $message
   * @param        $code
   * @param string $type
   */
  public static function critical($message, $code, $type = self::TYPE_GENERIC)
  {
    static::_log(self::LEVEL_CRITICAL, $message, $type, $code);
  }

  /**
   * Well, gone and dont it now, havent you!
   *
   * @param        $message
   * @param        $code
   * @param string $type
   */
  public static function fatal($message, $code, $type = self::TYPE_GENERIC)
  {
    static::_log(self::LEVEL_FATAL, $message, $type, $code);
  }

  /**
   * You need to brush up on your coding skills
   *
   * @param $message
   * @param $type
   * @param $code
   */
  public static function parseError($message, $type, $code)
  {
    static::_log(self::LEVEL_PARSE, $message, $type, $code);
  }


  /**
   * Notify of a deprecated action
   *
   * @param $message
   * @param $type
   * @param $code
   */
  public static function deprecated($message, $type, $code)
  {
    static::_log(self::LEVEL_DEPRECATED, $message, $type, $code);
  }

  /**
   * Let the world know about the various log messages
   *
   * Base handler for methods to filter through to
   *
   * @param $level
   * @param $message
   * @param $type
   * @param $code
   */
  protected static function _log($level, $message, $type, $code)
  {
    $backtrace   = \debug_backtrace();
    $sourceLine = $backtrace[1]['line'];
    $sourceFile = $backtrace[1]['file'];

    Events::trigger(
      Events::CUBEX_LOG, array(
                              'level'   => $level,
                              'message' => $message,
                              'type'    => $type,
                              'code'    => $code,
                              'file'    => $sourceFile,
                              'line'    => $sourceLine,
                         )
    );
  }
}
