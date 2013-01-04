<?php
/**
 * User: Brooke
 * Date: 01/01/13
 * Time: 19:39
 * Description:
 */

namespace Cubex\Event;

use Cubex\Data\HandlerInterface;

interface EventInterface extends HandlerInterface
{
  public function __construct($name, $args = array(), $callee = null);

  public function setName($name);

  public function getName();

  public function setCallee($callee);

  public function getCallee();

  public function getParams();

  public function getParam($name, $default = null);

  public function setParams($params);

  public function setParam($name, $value);
}
