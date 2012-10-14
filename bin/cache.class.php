<?php
/**
 * User: brooke.bryan
 * Date: 14/10/12
 * Time: 15:41
 * Description:
 */

$directory = dirname(dirname(__FILE__)) . '/cubes/';
$files     = array();
$files[]   = 'cubex/c.php';
$files[]   = 'cache/connection.php';
$files[]   = 'cache/memcache/connection.php';
$files[]   = 'database/connection.php';
$files[]   = 'database/mysql/connection.php';
$files[]   = 'session/container.php';

$final = '<?php
';
foreach($files as $file)
{
  $final .= "/* $file Start */\n";
  $script = file($directory . $file);
  $lines  = count($script);
  foreach($script as $line_num => $line)
  {
    if(empty($line) || $line == "\n") continue;
    if($line_num > 0) $final .= $line;
    if($line_num == $lines && $line == '?>') break;
  }
  $final .= "/* $file End */\n";
}

file_put_contents(
  dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR .
    'cubex' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'core.php',
  $final
);
