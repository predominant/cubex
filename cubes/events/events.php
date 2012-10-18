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

  protected static function Hook()
  {
    if(self::$_coathook === null) self::$_coathook = new Events();

    return self::$_coathook;
  }

  protected function addHook($event_name, $callback)
  {
    if(!is_callable($callback, true))
    {
      throw new \InvalidArgumentException(sprintf('Invalid callback: %s.', print_r($callback, true)));
    }
    $this->_hooks[$event_name][] = $callback;
  }

  protected function getCallbacks($event_name)
  {
    return isset($this->_hooks[$event_name]) ? $this->_hooks[$event_name] : array();
  }

  protected function fire($event_name)
  {
    foreach($this->getCallbacks($event_name) as $callback)
    {
      if(!is_callable($callback)) continue;
      call_user_func($callback);
    }
  }

  public static function fireEvent($name)
  {
    self::Hook()->fire($name);
  }

  public static function hookEvent($name, callable $callback)
  {
    self::Hook()->addHook($name, $callback);
  }
}
