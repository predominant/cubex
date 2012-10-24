<?php
/**
 * User: brooke.bryan
 * Date: 14/10/12
 * Time: 15:41
 * Description:
 */

$directory = dirname(dirname(__FILE__)) . '/cubes/';
$files     = array();
$files[]   = 'core.php';
$files[]   = 'cache/connection.php';
$files[]   = 'database/connection.php';
$files[]   = 'session/container.php';
$files[]   = 'data/handler.php';
$files[]   = 'http/request.php';
$files[]   = 'base/application.php';
$files[]   = '../application/loader.php';

$final = '<?php ';
foreach($files as $file)
{
  $final .= "/* $file */\n";
  $script = file($directory . $file);
  $lines  = count($script);
  foreach($script as $line_num => $line)
  {
    if(empty($line) || $line == "\n") continue;
    if($line_num > 0) $final .= $line;
    if($line_num == $lines && $line == '?>') break;
  }
}

/* Strip out comments */
$tokens        = token_get_all($final);
$cache_content = '';
foreach($tokens as $token)
{
  if(is_array($token))
  {
    if(in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) continue;
    $token = $token[1];
  }
  $cache_content .= $token;
}


$git_revision = shell_exec("git log -1 --pretty=format:%h");
$date         = date("d/m/y");
$time         = date("H:i");

$header = '
/**
 * Git Revision: ' . $git_revision . '
 * Date: ' . $date . '
 * Time: ' . $time . '
*/
';

$output = "<?php\n" . $header . substr($cache_content, 5);

$cachedir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'cache'  . DIRECTORY_SEPARATOR;

if(!file_exists($cachedir)) mkdir($cachedir, 0644);
file_put_contents($cachedir . 'core.php', $output);
