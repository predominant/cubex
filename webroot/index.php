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
require_once(dirname(dirname(__FILE__)) . '/cubex/cubex.php');
\Cubex\Cubex::boot();

if(RUN_PROFILER && function_exists("xhprof_disable"))
{
  $xhprofData  = xhprof_disable();
  $xhprofRoot = dirname(dirname(dirname(dirname(__FILE__)))) . '/facebook/xhprof';
  include_once $xhprofRoot . "/xhprof_lib/utils/xhprof_lib.php";
  include_once $xhprofRoot . "/xhprof_lib/utils/xhprof_runs.php";

  $xhprofRuns = new XHProfRuns_Default();
  $runId      = $xhprofRuns->save_run($xhprofData, "xhprof_cubex");

  echo '<br/><a target="_blank" href="http://www.xhprof.local/index.php?run=' . $runId;
  echo '&source=xhprof_cubex">Debug Data</a>';
}
