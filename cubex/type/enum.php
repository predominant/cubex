<?php
/**
 * File: reflection.php
 * Date: 02/01/13
 * Time: 11:16
 * @author: gareth.evans
 */

namespace Cubex\Type;

use Cubex\Type\Enum\Reflection;

if(class_exists("\\SplEnum"))
{
  abstract class Enum extends \SplEnum
  {

  }
}
else
{
  abstract class Enum extends Reflection
  {

  }
}
