<?php
/**
 * This is used in conjunction with php's built in webserver;
 * http://php.net/manual/en/features.commandline.webserver.php
 *
 * This is only for use in development so we force the environment setting
 *
 * To use run something similar to the following;
 * c:\php\php5.4.8\php.exe -S localhost:8000 -t webroot bin/cli_server_route.php
 *
 * There is currently no way to pass paramaters through to the routing script
 * in php 5.4's server. However, we want to be able to change the CUBEX_ENV
 * straight from a command prompt. To do this we've hijacked the 'user_agent'
 * php.ini directive. This can be set as follows (using above example);
 * c:\php\php5.4.8\php.exe -S localhost:8000 -d user_agent=local -t webroot ...
 *
 * There is no promise that the above won't affect the running of your script.
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

// See page comment for info on the below
$cubexEnv = ini_get('user_agent');
$_ENV['CUBEX_ENV'] = $cubexEnv ?: 'development';
$url = parse_url($_SERVER['REQUEST_URI']);
$_REQUEST['__path__'] = $url['path'];

require $_SERVER["DOCUMENT_ROOT"].'/index.php';
