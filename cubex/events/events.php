<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 20:09
 * Description:
 */

namespace Cubex\Events;

/**
 * Standard hooks for events
 */
final class Events
{

  const CUBEX_LAUNCH   = 'cubex.launch';
  const CUBEX_SHUTDOWN = 'cubex.shutdown';

  const CUBEX_RESPONSE_START = 'cubex.response.start';
  const CUBEX_RESPONSE_SENT  = 'cubex.response.sent';

  const CUBEX_DEBUG = 'cubex.debug';
  const CUBEX_LOG   = 'cubex.log';

  private static $_listeners = array();

  /**
   * Listen into an event
   *
   * @param          $eventName
   * @param callable $callback
   */
  public static function listen($eventName, callable $callback)
  {
    if(!isset(self::$_listeners[$eventName]))
    {
      self::$_listeners[$eventName] = array();
    }
    self::$_listeners[$eventName][] = $callback;
  }

  /**
   * Trigger an event
   *
   * @param       $eventName
   * @param array $args
   */
  public static function trigger($eventName, $args = array())
  {
    $listeners = isset(self::$_listeners[$eventName]) ? self::$_listeners[$eventName] : array();
    foreach($listeners as $listen)
    {
      if(!\is_callable($listen)) continue;
      \call_user_func_array($listen, $args);
    }
  }
}
