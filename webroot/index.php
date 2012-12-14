<?php
/**
 * User: brooke.bryan
 * Date: 13/10/12
 * Time: 19:06
 * Description: Web Handler for Cubex
 */

define('RUN_PROFILER', false);
if(RUN_PROFILER && function_exists('xhprof_enable')) xhprof_enable(XHPROF_FLAGS_NO_BUILTINS);

/**
 * Include and boot Cubex
 */
require_once(dirname(dirname(__FILE__)) . '/cubes/base/cubex.php');
\Cubex\Cubex::boot();

if(RUN_PROFILER && function_exists("xhprof_disable"))
{
  $xhprof_data = xhprof_disable();
  $XHPROF_ROOT = dirname(dirname(dirname(dirname(__FILE__)))) . '/facebook/xhprof';
  include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
  include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

  $xhprof_runs = new XHProfRuns_Default();
  $run_id      = $xhprof_runs->save_run($xhprof_data, "xhprof_cubex");

  echo '<br/><a target="_blank" href="http://www.xhprof.local/index.php?run=' . $run_id;
  echo '&source=xhprof_cubex">Debug Data</a>';
}
