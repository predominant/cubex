<?php
/**
 * User: Brooke Bryan
 * Date: 14/11/12
 * Time: 19:16
 * Description: Cubex Boot
 */

/**
 * Identity function, returns its argument unmodified.
 *
 * @param   item.
 * @return  mixed Unmodified argument.
 */
function id($o)
{
  return $o;
}

require_once('base/cubex.php');
\Cubex\Cubex::boot();
