<?php
/**
 * User: brooke.bryan
 * Date: 14/11/12
 * Time: 10:57
 * Description:
 */

namespace Cubex\Data;

class Model
{

  private $_attributes;
  private $_invalid_attributes;

  public function __construct($data=null)
  {
    if($data !== null)
    {
      $this->populate($data);
    }
  }

  public function populateAttribute($attribute_name,$value)
  {
    return $this->populate(array($attribute_name,$value));
  }

  public function populate($data=array())
  {
    if(is_array($data))
    {
      foreach($data as $attribute => $value)
      {
        $attr = isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : null;
        if($attr instanceof Attribute)
        {
          $attr->setData($value);
        }
      }
    }

    return $this;
  }

  public function attribute($name)
  {
    return isset($this->_attributes[$name]) ? $this->_attributes[$name] : false;
  }

  public function valid($attributes = null, $process_all_validators = false, $fail_first = false)
  {
    $valid = true;
    if($attributes === null)
    {
      $attributes = array_keys($this->_attributes);
    }

    if(is_array($attributes))
    {
      foreach($attributes as $attribute)
      {
        $attr = isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : null;
        if($attr instanceof Attribute)
        {
          if(!$attr->valid($process_all_validators))
          {
            $valid                       = false;
            $this->_invalid_attributes[] = $attribute;
            if($fail_first) return false;
          }
        }
      }
    }

    return $valid;
  }
}
