<?php
/**
 * User: brooke.bryan
 * Date: 28/10/12
 * Time: 01:19
 * Description:
 * Run: php bin/cli.php "Cubex\Application\Complex\Test" key=value --env=development
 */
namespace Cubex\Application\Complex;

class Test
{

  public function __construct($args)
  {
    echo "\n";
    foreach($args as $k => $v)
    {
      echo \Cubex\Cli\Shell::colourText($k, "light_green");
      echo " = ";
      echo \Cubex\Cli\Shell::colourText($v, "green");
      echo "\n";
    }
  }
}
