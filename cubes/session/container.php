<?php
/**
 * User: Brooke
 * Date: 14/10/12
 * Time: 01:03
 * Description:
 */

namespace Cubex\Session;

interface Container
{

  public function __construct(array $configuration);

  public function set($key, $data);

  public function get($key);

  public function destroy();
}
