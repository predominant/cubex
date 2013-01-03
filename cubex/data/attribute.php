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

  const SERIALIZATION_NONE = 'id';
  const SERIALIZATION_JSON = 'json';
  const SERIALIZATION_PHP  = 'php';

  private $_modified;
  private $_serializer;
  private $_name;
  private $_required;
  private $_validators;
  private $_filters;
  private $_options;
  private $_data;
  private $_originalData;
  private $_exceptions;
  private $_populated = false;

  public function __construct($name,
                              $required = false,
                              $validators = null,
                              $filters = null,
                              $options = null,
                              $data = null,
                              $serializer = self::SERIALIZATION_NONE)
  {
    $this->name($name);
    $this->required($required ? true : false);
    $this->addValidators($validators);
    $this->addFilters($filters);
    $this->setData($data);
    $this->setOptions($options);
    $this->setSerializer($serializer);
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
      $this->_originalData = $this->_data;
    }
    $this->_populated = $data !== null;
    $this->_data      = $data;
    $this->_modified  = true;

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
    if(\is_string($filters) || $filters instanceof Callback)
    {
      $filters = array($filters);
    }

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
  }

  public function filters($replaceFilters)
  {
    if($replaceFilters !== null && \is_array($replaceFilters))
    {
      $this->_filters = array();
      $this->addFilters($replaceFilters);

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
    if(\is_string($validators) || $validators instanceof Callback)
    {
      $validators = array($validators);
    }

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
  }

  public function validators($replaceValidators = null)
  {
    if($replaceValidators !== null && \is_array($replaceValidators))
    {
      $this->_validators = array();
      $this->addValidators($replaceValidators);

      return $this;
    }

    return $this->_validators;
  }

  public function valid($processAll = false)
  {
    if($this->required() && !$this->populated())
    {
      $this->_exceptions[] = new \Exception("Required Field " . $this->name());
      return false;
    }

    if(!\is_array($this->_validators))
    {
      return true;
    }

    $valid = true;
    foreach($this->_validators as $validator)
    {
      if($validator instanceof Callback)
      {
        $passed = false;
        try
        {
          $passed = $validator->process($this->data());
        }
        catch(\Exception $e)
        {
          $this->_exceptions[] = $e;
        }
        if(!$passed)
        {
          $valid = false;
          if(!$processAll)
          {
            break;
          }
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
    return $this->_originalData;
  }

  public function revert()
  {
    $this->setData($this->_originalData);
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

  public function setSerializer($serializer)
  {
    $this->_serializer = $serializer;

    return $this;
  }

  public function getSerializer()
  {
    return $this->_serializer;
  }

  public function serialize()
  {
    switch($this->getSerializer())
    {
      case self::SERIALIZATION_JSON:
        return json_encode($this->rawData());
      case self::SERIALIZATION_PHP:
        return serialize($this->rawData());
    }
    return $this->rawData();
  }

  public function unserialize($data)
  {
    switch($this->getSerializer())
    {
      case self::SERIALIZATION_JSON:
        return json_decode($data);
      case self::SERIALIZATION_PHP:
        return unserialize($data);
    }

    return $data;
  }
}
