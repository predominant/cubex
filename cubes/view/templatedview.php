<?php
/**
 * User: brooke.bryan
 * Date: 12/12/12
 * Time: 20:12
 * Description:
 */

namespace Cubex\View;

use Cubex\Cubex;

class TemplatedView extends View
{

  protected $_templates_path;
  protected $_template_file;
  /** @var Template */
  protected $_template;

  /**
   * Automated way to pick out the correct views folder
   */
  /*public function __construct($template_file = null)
  {
    if($template_file !== null)
    {
      $backtrace = debug_backtrace(2);
      if(isset($backtrace[1]))
      {
        $this->calculateTemplate($backtrace[1]['class']);
      }
      else
      {
        $this->_templates_path = Cubex::config('general')->getStr('include_path');
      }
      $this->_template_file = $template_file;
    }
  }*/

  public function setTemplatesPath($path)
  {
    $this->_templates_path = $path;
  }


  protected function calculateTemplate($class = null)
  {
    $reflector = new \ReflectionClass($class === null ? \get_class($this) : $class);
    $ns        = ltrim($reflector->getName(), "\\");
    $ns_parts  = explode('\\', $ns);

    foreach($ns_parts as $part)
    {
      if($class === null)
      {
        \array_shift($ns_parts);
      }

      $part = \strtolower($part);
      if(\in_array($part, array('controllers', 'views')))
      {
        break;
      }

      if(\substr($part, -10) == 'controller')
      {
        break;
      }

      if($class !== null)
      {
        \array_shift($ns_parts);
      }
    }

    $this->_templates_path = dirname($reflector->getFileName());
    for($ii = 0; $ii < count($ns_parts); $ii++)
    {
      $this->_templates_path = dirname($this->_templates_path);
    }

    $this->setTemplateFile(strtolower(implode('\\', $ns_parts)));

    return $this;
  }

  protected function setTemplateFile($template)
  {
    $this->_template_file = $template;

    return $this;
  }

  public function render()
  {
    if($this->_template_file === null)
    {
      $this->calculateTemplate();
    }

    $this->_templates_path = rtrim($this->_templates_path, '/\\') . DIRECTORY_SEPARATOR;

    $this->_template = new Template($this->_template_file, $this->_templates_path . 'templates');
    $this->_template->setDispatcher($this);
    foreach($this as $k => $v)
    {
      $this->_template->setData($k, $v);
    }

    return $this->_template->render();
  }
}
