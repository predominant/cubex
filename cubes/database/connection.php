<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 01:03
 * Description:
 */

namespace Cubex\Database;

interface Connection extends \Cubex\Base\DataConnection
{
  public function __construct(array $configuration);
}
