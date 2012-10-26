<?php
/**
 * User: brooke.bryan
 * Date: 18/10/12
 * Time: 20:09
 * Description:
 */

namespace Cubex\Application\Complex;

class Events extends \Cubex\Events\Events
{

  public static function createHooks()
  {
    self::hookEvent("pageStarted", function () { ExternalEvents::ExternalPageLaunch(); });
  }

  public static function pageStarted()
  {
    self::fireEvent("pageStarted");
  }
}
