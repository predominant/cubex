<?php
/**
 * This gives us a convenient way to always have an Enum object available and
 * utilise Spl Types if available. It does kick up a bit of a fuss in some IDEs
 * as it sees two classes with the same name, but we know this isn't an issue as
 * the code is down there :)
 *
 * We also wrap the SplEnum class to stop IDEs thinkging that the constructor
 * paramaters are necessary.
 *
 * File: reflection.php
 * Date: 02/01/13
 * Time: 11:16
 * @author: gareth.evans
 */

namespace Cubex\Type;

use Cubex\Type\Enum\Reflection;

if(class_exists("\\SplEnum"))
{
  class SplWrapper extends \SplEnum
  {
    public function __construct($enum = null, $strict = false)
    {
      parent::__construct($enum, $strict);
    }
  }

  abstract class Enum extends SplWrapper
  {

  }
}
else
{
  abstract class Enum extends Reflection
  {

  }
}
