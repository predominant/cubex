<?php
/**
 * User: brooke.bryan
 * Date: 13/12/12
 * Time: 19:35
 * Description:
 */
namespace Cubex\Logger;

class Log
{

  const LEVEL_INFO     = 'info';
  const LEVEL_SUCCESS  = 'success';
  const LEVEL_WARNING  = 'warning';
  const LEVEL_CRITICAL = 'critical';
  const LEVEL_FATAL    = 'fatal';
  const LEVEL_PARSE    = 'parse';

  const TYPE_GENERIC     = 'generic';
  const TYPE_DEBUG       = 'debug';
  const TYPE_APPLICATION = 'application';
  const TYPE_CONTROLLER  = 'controller';
  const TYPE_WIDGET      = 'widget';
  const TYPE_MODULE      = 'module';
  const TYPE_QUERY       = 'query';
  const TYPE_API         = 'api';

  public static function Info($message, $code, $type = self::TYPE_GENERIC)
  {
    self::Log(self::LEVEL_INFO, $message, $type, $code);
  }

  public static function Success($message, $code, $type = self::TYPE_GENERIC)
  {
    self::Log(self::LEVEL_SUCCESS, $message, $type, $code);
  }

  public static function Warning($message, $code, $type = self::TYPE_GENERIC)
  {
    self::Log(self::LEVEL_WARNING, $message, $type, $code);
  }

  public static function Critical($message, $code, $type = self::TYPE_GENERIC)
  {
    self::Log(self::LEVEL_CRITICAL, $message, $type, $code);
  }

  public static function Fatal($message, $code, $type = self::TYPE_GENERIC)
  {
    self::Log(self::LEVEL_FATAL, $message, $type, $code);
  }

  public static function ParseError($message, $type, $code)
  {
    self::Log(self::LEVEL_PARSE, $message, $type, $code);
  }

  protected static function Log($level, $message, $type, $code)
  {
    $backtrace   = debug_backtrace();
    $source_line = $backtrace[1]['line'];
    $source_file = $backtrace[1]['file'];
  }
}
