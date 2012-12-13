<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 12:06
 * Description:
 */

namespace Cubex\Data;

use Cubex\Base\Callback;

class Attribute
{

  private $_modified;
  private $_name;
  private $_required;
  private $_validators;
  private $_filters;
  private $_options;
  private $_data;
  private $_original_data;
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
    $this->_modified = false;
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
    if(\is_string($name))
    {
      $this->_name = $name;

      return $this;
    }

    return $this->getName();
  }

  public function id()
  {
    return \str_replace('_', '-', $this->name());
  }

  public function required($set = null)
  {
    if(\is_bool($set))
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
    if(!$this->isModified())
    {
      $this->_original_data = $this->_data;
    }
    $this->_populated = $data !== null;
    $this->_data      = $data;
    $this->_modified = true;

    return $this;
  }

  public function rawData()
  {
    return $this->_data;
  }

  public function data()
  {
    if(!\is_array($this->_filters)) return $this->_data;

    $data = $this->_data;
    foreach($this->_filters as $filter)
    {
      if($filter instanceof Callback)
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

  public function addFilter(Callback $filter)
  {
    $this->_filters[] = $filter;

    return $this;
  }

  public function addFilters($filters)
  {
    if(\is_array($filters))
    {
      foreach($filters as $filter)
      {
        if(\is_string($filter))
        {
          $this->addFilter(Callback::_($filter, array(), 'filter'));
        }
        else if(\is_array($filter))
        {
          if(isset($filter[0]) && \is_array($filter[0]))
          {
            $this->addFilter(Callback::_($filter[0], $filter[1], 'filter'));
          }
        }
        else if($filter instanceof Callback)
        {
          $this->addFilter($filter);
        }
      }
    }
    else if(\is_string($filters))
    {
      $this->addFilter(Callback::_($filters, array(), 'filter'));
    }
    else if($filters instanceof Callback)
    {
      $this->addFilter($filters);
    }
  }

  public function filters($replace_filters)
  {
    if($replace_filters !== null && \is_array($replace_filters))
    {
      $this->_filters = array();
      $this->addFilters($replace_filters);

      return $this;
    }

    return $this->_filters;
  }

  public function addValidator(Callback $validator)
  {
    $this->_validators[] = $validator;

    return $this;
  }

  public function addValidators($validators)
  {
    if(\is_array($validators))
    {
      foreach($validators as $validator)
      {
        if(\is_string($validator))
        {
          $this->addValidator(Callback::_($validator, array(), "validator"));
        }
        else if(\is_array($validator))
        {
          if(isset($validator[0]) && \is_array($validator[0]))
          {
            $this->addValidator(Callback::_($validator[0], $validator[1], "validator"));
          }
        }
        else if($validator instanceof Callback)
        {
          $this->addValidator($validator);
        }
      }
    }
    else if(\is_string($validators))
    {
      $this->addValidator(Callback::_($validators, array(), "validator"));
    }
    else if($validators instanceof Callback)
    {
      $this->addValidator($validators);
    }
  }

  public function validators($replace_validators = null)
  {
    if($replace_validators !== null && \is_array($replace_validators))
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
    if(!\is_array($this->_validators)) return true;

    $valid = true;
    foreach($this->_validators as $validator)
    {
      if($validator instanceof Callback)
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
    return \is_array($this->_exceptions) ? $this->_exceptions : array();
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

  public function originalData()
  {
    return $this->_original_data;
  }

  public function revert()
  {
    $this->setData($this->_original_data);
    $this->unsetModified();
    return true;
  }

  public function isModified()
  {
    return $this->_modified;
  }

  public function setModified()
  {
    $this->_modified = true;
    return $this;
  }

  public function unsetModified()
  {
    $this->_modified = false;
    return $this;
  }
}
