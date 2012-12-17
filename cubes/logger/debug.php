<?php
/**
 * User: Brooke
 * Date: 16/12/12
 * Time: 14:43
 * Description:
 */

namespace Cubex\Logger;

/**
 * Standard debug access
 */
use Cubex\Events\Events;

class Debug extends Log
{
  protected static $_enabled;

  /**
   * Enable debug logging
   *
   * @return bool success
   */
  public static function enable()
  {
    self::$_enabled = true;
    return true;
  }

  /**
   * Disable debug logging
   *
   * @return bool success
   */
  public static function disable()
  {
    self::$_enabled = false;
    return true;
  }

  /**
   * Handle all debug logging, replace standard log logic to keep separate
   *
   * @param $level
   * @param $message
   * @param $type
   * @param $code
   */
  protected static function _log($level, $message, $type, $code)
  {
    $backtrace   = \debug_backtrace();
    $source_line = $backtrace[1]['line'];
    $source_file = $backtrace[1]['file'];

    Events::trigger(
      Events::CUBEX_DEBUG, array(
                                'level'   => $level,
                                'message' => $message,
                                'type'    => $type,
                                'code'    => $code,
                                'file'    => $source_file,
                                'line'    => $source_line,
                           )
    );
  }
}
