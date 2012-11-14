<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 18:47
 * Description:
 */

namespace Cubex\Module\User\Model;
use \Cubex\Data\Attribute as Attribute;

class User extends \Cubex\Data\Model
{
  /**
   * @public varchar(70) $username
   * @public varchar(32) $name
   * @public $names mixed
   */

  public $name,$username;

  public function __construct()
  {
    $this->addAttribute(new Attribute('name'));
    $this->unsetAttributePublics();
  }
}
