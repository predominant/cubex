<?php
/**
 * User: brooke
 * Date: 26/11/12
 * Time: 09:12
 * Description:
 */

namespace Cubex\Data;

abstract class Edge
{

  protected $_edge_type = null;

  public static function edgeType()
  {
    return \strtolower(\end(\explode('\\', \get_called_class()))) . ':' . \substr(\md5(\get_called_class()), 22) . ':';
  }

  abstract public function getEdges($source_id, $edge_type = null);

  abstract public function addEdge($source_id, $relation_id, $edge_type);
}
