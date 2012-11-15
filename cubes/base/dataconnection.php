<?php
/**
 * User: brooke.bryan
 * Date: 15/11/12
 * Time: 19:19
 * Description:
 */
namespace Cubex\Base;

interface DataConnection
{

  public function connect();

  public function disconnect();
}
