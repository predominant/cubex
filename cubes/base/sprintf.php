<?php
/**
 * User: Brooke
 * Date: 18/11/12
 * Time: 12:38
 * Description:
 */

namespace Cubex\Base;

class Sprintf
{

  /*
   * Query Patterns
   *
   * LIKE Query
   * %~ = '%value%'
   * %> = 'value%'
   * %< = '%value'
   *
   * %f = (float)value
   * %d = (int)value
   * %s = escapeString(value)
   *
   * Column Name formatters applied to both table and column
   * %T = table name
   * %C = column name
   *
   * Implode an array of the type
   * %Ld = implode int array
   * %Lf = implode int array
   * %Ls = implode string array
   * %LC = implode column names
   *
   * If null is passed as the value, the output will be NULL rather than a formatted value
   * %nd = nullable int
   * %ns = nullable string
   * %nf = nullable float
   *
   * %=d = nullable test int (e.g. = 12 | IS NULL)
   * %=f = nullable test float (e.g. = 12.1 | IS NULL)
   * %=s = nullable test string (e.g. = 12 | IS NULL)
   *
   * Q. will loop through each item and build up a query for a match
   * %QO = Query Object
   * %QA = Query Array
   *
   */
  final public static function parseQuery($connection, $args)
  {
    return self::call(array("\\Cubex\\Base\\Sprintf", "query"), $connection, $args);
  }

  final public static function call($callback, $user_data, $argv)
  {
    $argc    = count($argv);
    $arg     = 0;
    $pattern = $argv[0];
    $len     = strlen($pattern);
    $conv    = false;

    for($pos = 0; $pos < $len; $pos++)
    {
      $c = $pattern[$pos];

      if($conv)
      {
        //  We could make a greater effort to support formatting modifiers,
        //  but they really have no place in semantic string formatting.
        if(strpos("'-0123456789.\$+", $c) !== false)
        {
          throw new \Exception("Sprintf::call() does not support the `%{$c}' modifier.");
        }

        if($c != '%')
        {
          $conv = false;

          $arg++;
          if($arg >= $argc)
          {
            throw new \Exception("Too few arguments to Sprintf::call().");
          }

          $callback($user_data, $pattern, $pos, $argv[$arg], $len);
        }
      }

      if($c == '%')
      {
        //  If we have "%%", this encodes a literal percentage symbol, so we are
        //  no longer inside a conversion.
        $conv = !$conv;
      }
    }

    if($arg != ($argc - 1))
    {
      throw new \Exception("Too many arguments to Sprintf::call().");
    }

    $argv[0] = $pattern;

    return call_user_func_array('sprintf', $argv);
  }

  public function sprintfCheckType($value, $type, $query)
  {
    switch($type)
    {
      case 'Ld':
      case 'Ls':
      case 'LC':
        if(!is_array($value))
        {
          throw new \Exception("Expected array argument for %{$type} conversion in {$query}");
        }
        break;
      case 'QA':
        if(!is_array($value))
        {
          throw new \Exception("Expected array argument for %{$type} conversion in {$query}");
        }
        break;
      case 'QO':
        if(!is_object($value))
        {
          throw new \Exception("Expected array argument for %{$type} conversion in {$query}");
        }
        break;
    }
  }

  final private static function query(\Cubex\Base\DataConnection $connection, &$pattern, &$pos, &$value,
                                      &$length)
  {
    $type = $pattern[$pos];
    $next = (strlen($pattern) > $pos + 1) ? $pattern[$pos + 1] : null;

    $nullable = false;
    $done     = false;

    $prefix = '';

    switch($type)
    {
      case '=': // Nullable test
        switch($next)
        {
          case 'd':
          case 'f':
          case 's':
            $pattern = substr_replace($pattern, '', $pos, 1);
            $length  = strlen($pattern);
            $type    = 's';
            if($value === null)
            {
              $value = 'IS NULL';
              $done  = true;
            }
            else
            {
              $prefix = '= ';
              $type   = $next;
            }
            break;
          default:
            throw new \Exception('Unknown conversion, try %=d, %=s, or %=f.');
        }
        break;

      case 'n': // Nullable...
        switch($next)
        {
          case 'd': //  ...integer.
          case 'f': //  ...float.
          case 's': //  ...string.
            $pattern  = substr_replace($pattern, '', $pos, 1);
            $length   = strlen($pattern);
            $type     = $next;
            $nullable = true;
            break;
          default:
            throw new \Exception('Unknown conversion, try %nd or %ns.');
        }
        break;

      case 'Q': //Query...
        self::sprintfCheckType($value, "Q{$next}", $pattern);
        $pattern = substr_replace($pattern, '', $pos, 1);
        $length  = strlen($pattern);
        $type    = 's';
        $done    = true;
        switch($next)
        {
          case 'O': //Object
          case 'A': //Array
            $check_match = $next == 'O' && method_exists($value, 'getMatchType');
            $qu          = array();
            foreach($value as $k => $v)
            {
              if(is_int($v)) $val = (int)$v;
              else if(is_float($v)) $val = (float)$v;
              else if(is_bool($v)) $val = (int)$v;
              else $val = "'" . $connection->escapeString($v) . "'";

              if($check_match)
              {
                switch($value->getMatchType($k))
                {
                  case '~':
                    $qu[] = $connection->escapeColumnName($k) . " LIKE '%" . $connection->escapeString($v) . "%'";
                    break;
                  case '>':
                    $qu[] = $connection->escapeColumnName($k) . " LIKE '" . $connection->escapeString($v) . "%'";
                    break;
                  case '<':
                    $qu[] = $connection->escapeColumnName($k) . " LIKE '%" . $connection->escapeString($v) . "'";
                    break;
                  case '=':
                    $qu[] = $connection->escapeColumnName($k) . " = " . $val;
                    break;
                }
              }
              else
              {
                $qu[] = $connection->escapeColumnName($k) . " = " . $val;
              }
            }
            $value = implode(' AND ', $qu);
            break;
          default:
            throw new \Exception("Unknown conversion %Q{$next}.");
        }
        break;

      case 'L': // List of..
        self::sprintfCheckType($value, "L{$next}", $pattern);
        $pattern = substr_replace($pattern, '', $pos, 1);
        $length  = strlen($pattern);
        $type    = 's';
        $done    = true;

        switch($next)
        {
          case 'd': //  ...integers.
            $value = implode(', ', array_map('intval', $value));
            break;
          case 'f': //  ...floats.
            $value = implode(', ', array_map('floatval', $value));
            break;
          case 's': // ...strings.
            foreach($value as $k => $v)
            {
              $value[$k] = "'" . $connection->escapeString($v) . "'";
            }
            $value = implode(', ', $value);
            break;
          case 'C': // ...columns.
            foreach($value as $k => $v)
            {
              $value[$k] = $connection->escapeColumnName($v);
            }
            $value = implode(', ', $value);
            break;
          default:
            throw new \Exception("Unknown conversion %L{$next}.");
        }
        break;
    }

    if(!$done)
    {
      self::sprintfCheckType($value, $type, $pattern);
      switch($type)
      {
        case 's': // String
          if($nullable && $value === null)
          {
            $value = 'NULL';
          }
          else
          {
            $value = "'" . $connection->escapeString($value) . "'";
          }
          $type = 's';
          break;

        case '~': // Like Substring
        case '>': // Like Prefix
        case '<': // Like Suffix
          $value = $connection->escapeStringForLikeClause($value);
          switch($type)
          {
            case '~':
              $value = "'%" . $value . "%'";
              break;
            case '>':
              $value = "'" . $value . "%'";
              break;
            case '<':
              $value = "'%" . $value . "'";
              break;
          }
          $type = 's';
          break;

        case 'f': // Float
          if($nullable && $value === null)
          {
            $value = 'NULL';
          }
          else
          {
            $value = (float)$value;
          }
          $type = 's';
          break;

        case 'd': // Integer
          if($nullable && $value === null)
          {
            $value = 'NULL';
          }
          else
          {
            $value = (int)$value;
          }
          $type = 's';
          break;

        case 'T': // Table
        case 'C': // Column
          $value = $connection->escapeColumnName($value);
          $type  = 's';
          break;

        default:
          throw new \Exception("Unknown conversion '%{$type}'.");

      }
    }

    if($prefix)
    {
      $value = $prefix . $value;
    }
    $pattern[$pos] = $type;
  }
}
