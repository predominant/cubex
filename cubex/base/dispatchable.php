<?php
/**
 * User: Brooke
 * Date: 01/01/13
 * Time: 22:40
 * Description:
 */
namespace Cubex\Base;

use Cubex\Http\Request;
use Cubex\Http\Response;

interface Dispatchable
{
  public function dispatch(Request $request, Response $response);
}
