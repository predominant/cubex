<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 18:47
 * Description:
 */

namespace Cubex\Module\User\Model;

class User extends \Cubex\Data\SQLModel
{

  /**
   * @public varchar(70) $username
   * @public varchar(32) $name
   * @public $names mixed
   */

  public $name;
  public $username;
  public $names;

  public function __construct()
  {
    parent::__construct();
    $this->addAttributeFilter('name', \Cubex\Base\Callback::filter("trim"));
    $this->addAttributeValidator('username', \Cubex\Base\Callback::validator("email"));
  }

  public function dataConnection()
  {
    return \Cubex\Cubex::db('db');
  }

  public function isBusiness()
  {
    return $this->type == 'business';
  }
}
