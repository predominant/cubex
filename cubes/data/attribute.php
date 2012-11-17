<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 12:06
 * Description:
 */

namespace Cubex\Data;

class Attribute
{

  private $_name;
  private $_required;
  private $_validators;
  private $_filters;
  private $_options;
  private $_data;
  private $_exceptions;
  private $_populated = false;

  public function __construct($name,
                              $required = false,
                              $validators = null,
                              $filters = null,
                              $options = null,
                              $data = null)
  {
    $this->name($name);
    $this->required($required ? true : false);
    $this->addValidators($validators);
    $this->addFilters($filters);
    $this->setData($data);
    $this->setOptions($options);
  }

  public function __toString()
  {
    return $this->_name;
  }

  public function populated()
  {
    return $this->_populated ? true : false;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function name($name = null)
  {
    if(is_string($name))
    {
      $this->_name = $name;

      return $this;
    }

    return $this->getName();
  }

  public function id()
  {
    return str_replace('_', '-', $this->name());
  }

  public function required($set = null)
  {
    if(is_bool($set))
    {
      $this->_required = $set;

      return $this;
    }

    return $this->_required ? true : false;
  }

  public function isEmpty()
  {
    return empty($this->_data);
  }

  public function setData($data)
  {
    $this->_populated = !is_null($data);
    $this->_data      = $data;

    return $this;
  }

  public function rawData()
  {
    return $this->_data;
  }

  public function data()
  {
    if(!is_array($this->_filters)) return $this->_data;

    $data = $this->_data;
    foreach($this->_filters as $filter)
    {
      if($filter instanceof \Cubex\Base\Callback)
      {
        $data = $filter->process($data);
      }
    }

    return $data;
  }

  public function setOptions($options)
  {
    $this->_options = $options;

    return $this;
  }

  public function addOption($option)
  {
    $this->_options[] = $option;

    return $this;
  }

  public function options()
  {
    return $this->_options;
  }

  public function addFilter(\Cubex\Base\Callback $filter)
  {
    $this->_filters[] = $filter;

    return $this;
  }

  public function addFilters($filters)
  {
    if(is_array($filters))
    {
      foreach($filters as $filter)
      {
        if(is_string($filter))
        {
          $this->addFilter(\Cubex\Base\Callback::_($filter, array(), 'filter'));
        }
        else if(is_array($filter))
        {
          if(isset($filter[0]) && is_array($filter[0]))
          {
            $this->addFilter(\Cubex\Base\Callback::_($filter[0], $filter[1], 'filter'));
          }
        }
        else if($filter instanceof \Cubex\Base\Callback)
        {
          $this->addFilter($filter);
        }
      }
    }
    else if(is_string($filters))
    {
      $this->addFilter(\Cubex\Base\Callback::_($filters, array(), 'filter'));
    }
    else if($filters instanceof \Cubex\Base\Callback)
    {
      $this->addFilter($filters);
    }
  }

  public function filters($replace_filters)
  {
    if(!is_null($replace_filters) && is_array($replace_filters))
    {
      $this->_filters = array();
      $this->addFilters($replace_filters);

      return $this;
    }

    return $this->_filters;
  }

  public function addValidator(\Cubex\Base\Callback $validator)
  {
    $this->_validators[] = $validator;

    return $this;
  }

  public function addValidators($validators)
  {
    if(is_array($validators))
    {
      foreach($validators as $validator)
      {
        if(is_string($validator))
        {
          $this->addValidator(\Cubex\Base\Callback::_($validator, array(), "validator"));
        }
        else if(is_array($validator))
        {
          if(isset($validator[0]) && is_array($validator[0]))
          {
            $this->addValidator(\Cubex\Base\Callback::_($validator[0], $validator[1], "validator"));
          }
        }
        else if($validator instanceof \Cubex\Base\Callback)
        {
          $this->addValidator($validator);
        }
      }
    }
    else if(is_string($validators))
    {
      $this->addValidator(\Cubex\Base\Callback::_($validators, array(), "validator"));
    }
    else if($validators instanceof \Cubex\Base\Callback)
    {
      $this->addValidator($validators);
    }
  }

  public function validators($replace_validators = null)
  {
    if(!is_null($replace_validators) && is_array($replace_validators))
    {
      $this->_validators = array();
      $this->addValidators($replace_validators);

      return $this;
    }

    return $this->_validators;
  }

  public function valid($process_all = false)
  {
    if($this->required() && !$this->populated())
    {
      $this->_exceptions[] = new \Exception("Required Field " . $this->name());

      return false;
    }
    if(!is_array($this->_validators)) return true;

    $valid = true;
    foreach($this->_validators as $validator)
    {
      if($validator instanceof \Cubex\Base\Callback)
      {
        $passed = false;
        try
        {
          $passed = $validator->process($this->data());
          if(!$passed)
          {
            throw new \Exception("Validation failed");
          }
        }
        catch(\Exception $e)
        {
          $this->_exceptions[] = $e;
        }
        if(!$passed)
        {
          $valid = false;
          if(!$process_all) break;
        }
      }
    }

    return $valid;
  }

  public function exceptions()
  {
    return is_array($this->_exceptions) ? $this->_exceptions : array();
  }

  public function errors()
  {
    $errors = array();

    foreach($this->exceptions() as $e)
    {
      if($e instanceof \Exception)
      {
        $errors[] = $e->getMessage();
      }
    }

    return $errors;
  }
}
