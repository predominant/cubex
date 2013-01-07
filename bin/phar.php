<?php
/**
 * User: brooke.bryan
 * Date: 07/01/13
 * Time: 15:13
 * Description: You must update your php.ini to change phar.readonly = Off
 */

ini_set('display_errors', true);
error_reporting(E_ALL);

$phar = new Phar(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cubex.phar');

echo "Creating: " . dirname(__DIR__) . DIRECTORY_SEPARATOR . "cubex.phar\n";

$phar->buildFromDirectory(
  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cubex'
);
$phar->setStub($phar->createDefaultStub('cubex.php'));
