<?php
/**
 * User: brooke.bryan
 * Date: 07/01/13
 * Time: 18:19
 * Description:
 */
namespace Cubex\Model;

class APIModel extends Model
{
  /**
   * @var \Cubex\Model\DataModel
   */
  protected $_passthru;
  protected $_passthruClass;

  protected function _canPassthru()
  {
    if($this->_passthru !== null)
    {
      return true;
    }
    else if($this->_passthruClass === null)
    {
      return false;
    }
    else if(class_exists($this->_passthruClass))
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  protected function _pass()
  {
    if($this->_passthru === null)
    {
      $this->_passthru = $this->_newPassthru();
    }
    return $this->_passthru;
  }

  protected function _newPassthru()
  {
    if($this->_canPassthru())
    {
      $passthru = new $this->_passthruClass();
      if($passthru instanceof DataModel)
      {
        return $passthru;
      }
      else
      {
        throw new \Exception("Passthru class does not extend DataModel");
      }
    }
    else
    {
      throw new \Exception("No passthru class defined");
    }
  }

  /**
   * @param $name
   * @param $value
   *
   * @return bool|Model|mixed
   */
  public function __set($name, $value)
  {
    if($this->_canPassthru())
    {
      $this->_pass()->$name = $value;
    }
    return $this->call("set" . \ucwords($name), array($value));
  }

  /**
   * @param       $id
   * @param array $attributes
   *
   * @return static
   */
  public static function load($id, $attributes = ['*'])
  {
    $entity = new static;
    $entity->_load($id, $attributes);
    return $entity;
  }

  /**
   * @param       $id
   * @param array $attributes
   *
   * @return $this
   */
  protected function _load($id, $attributes = ['*'])
  {
    $this->_passthru = $this->_newPassthru();
    $this->_passthru->load($id, $attributes);
    $this->hydrate($this->_passthru);
    return $this;
  }

  public function save()
  {
    $this->_passthru->saveChanges();
  }

  public function delete()
  {
    $this->_passthru->delete();
  }
}
