<?php
/**
 * User: brooke.bryan
 * Date: 28/10/12
 * Time: 01:11
 * Description: Script Loader
 *
 *
 * USAGE:
 * php /path_to_cubex/bin/cli.php "class_name" --env=ENVIRONENT
 *
 */

$script    = $_REQUEST['__path__'] = '';
$arguments = array();

foreach($argv as $argi => $arg)
{
  if(substr($arg, 0, 6) == '--env=')
  {
    $_ENV['CUBEX_ENV'] = substr($arg, 6);
  }
  else if($argi == 1)
  {
    $script = $_REQUEST['__path__'] = $arg;
  }
  else if($argi > 1)
  {
    list($k, $v) = explode('=', $arg, 2);
    $arguments[$k] = $_REQUEST[$k] = $_GET[$k] = $v;
  }
}

$_SERVER['CUBEX_CLI'] = true;

require_once(dirname(dirname(__FILE__)) . '/cubex/cubex.php');
chdir(dirname(__FILE__));
\Cubex\Cubex::boot();

if(class_exists($script))
{
  new $script($arguments);
}
else
{
  \Cubex\Cubex::fatal($script . " could not be loaded");
}
