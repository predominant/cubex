<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 20:09
 * Description:
 */

namespace Cubex\Events;

final class Events
{

  const CUBEX_LAUNCH   = 'cubex.launch';
  const CUBEX_SHUTDOWN = 'cubex.shutdown';

  const CUBEX_RESPONSE_START = 'cubex.response.start';
  const CUBEX_RESPONSE_SENT  = 'cubex.response.sent';

  const CUBEX_DEBUG = 'cubex.debug';
  const CUBEX_LOG   = 'cubex.log';

  private static $_listeners = array();

  public static function listen($event_name, callable $callback)
  {
    if(!isset(self::$_listeners[$event_name]))
    {
      self::$_listeners[$event_name] = array();
    }
    self::$_listeners[$event_name][] = $callback;
  }

  public static function trigger($event_name, $args = array())
  {
    $listeners = isset(self::$_listeners[$event_name]) ? self::$_listeners[$event_name] : array();
    foreach($listeners as $listen)
    {
      if(!\is_callable($listen)) continue;
      \call_user_func_array($listen, $args);
    }
  }
}
