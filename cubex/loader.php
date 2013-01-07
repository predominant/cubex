<?php
/**
 * User: brooke.bryan
 * Date: 07/01/13
 * Time: 15:51
 * Description:
 */

if(file_exists(dirname(dirname(__FILE__)) . '/cubex.phar'))
{
  define('CUBEX_ROOT', dirname(dirname(__FILE__)));
  require_once dirname(dirname(__FILE__)) . '/cubex.phar';
}
else
{
  require_once __DIR__ . '/cubex.php';
}
\Cubex\Cubex::boot();
