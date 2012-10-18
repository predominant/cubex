<?php
/**
 * This is used in conjunction with php's built in webserver;
 * http://php.net/manual/en/features.commandline.webserver.php
 *
 * This is only for use in development so we force the environment setting
 *
 * To use run something similar to the following;
 * c:\php\php5.4.8\php.exe -S localhost:8000 -t webroot bin/cli_server_route.php
 */
if(php_sapi_name() !== 'cli-server')
{
  throw new RuntimeException(
    'This file should be served using php\'s built in server', 500
  );
}

if(!isset($_SERVER["DOCUMENT_ROOT"]))
{
  throw new RuntimeException(
    'We need $_SERVER["DOCUMENT_ROOT"] set to route you', 500
  );
}

$_ENV['CUBEX_ENV'] = 'development';
$url = parse_url($_SERVER['REQUEST_URI']);
$_REQUEST['__path__'] = $url['path'];

require $_SERVER["DOCUMENT_ROOT"].'/index.php';
