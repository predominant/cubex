<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 20:09
 * Description:
 */

namespace Cubex\Events;

class Events
{

  private static $_coathook = null;
  private $_hooks;

  final protected static function Hook()
  {
    if(self::$_coathook === null) self::$_coathook = new Events();

    return self::$_coathook;
  }

  final protected function addHook($event_name, $callback)
  {
    if(!\is_callable($callback, true))
    {
      throw new \InvalidArgumentException(\sprintf('Invalid callback: %s.', print_r($callback, true)));
    }
    $this->_hooks[$event_name][] = $callback;
  }

  final protected function getCallbacks($event_name)
  {
    return isset($this->_hooks[$event_name]) ? $this->_hooks[$event_name] : array();
  }

  final protected function fire($event_name)
  {
    foreach($this->getCallbacks($event_name) as $callback)
    {
      if(!\is_callable($callback)) continue;
      \call_user_func($callback);
    }
  }

  public static function fireEvent($name)
  {
    self::Hook()->fire($name);
  }

  final public static function hookEvent($name, callable $callback)
  {
    self::Hook()->addHook($name, $callback);
  }

  public static function createHooks()
  {
  }
}
