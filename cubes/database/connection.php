<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 01:03
 * Description:
 */

namespace Database;

interface Connection
{
  public function __construct(array $configuration);
}
