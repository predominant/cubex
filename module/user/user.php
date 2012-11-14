<?php
/**
 * User: brooke
 * Date: 11/11/12
 * Time: 07:47
 * Description:
 */

namespace Cubex\Module\User;

class User extends \Cubex\Base\Module
{

  public function authenticate($username, $password)
  {
    return true;
  }
}
