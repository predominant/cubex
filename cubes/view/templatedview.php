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

  protected $_templatesPath;
  protected $_templateFile;
  /** @var Template */
  protected $_template;

  /**
   * Automated way to pick out the correct views folder
   */
  /*public function __construct($templateFile = null)
  {
    if($templateFile !== null)
    {
      $backtrace = debug_backtrace(2);
      if(isset($backtrace[1]))
      {
        $this->calculateTemplate($backtrace[1]['class']);
      }
      else
      {
        $this->_templatesPath = Cubex::config('general')->getStr('include_path');
      }
      $this->_templateFile = $templateFile;
    }
  }*/

  public function setTemplatesPath($path)
  {
    $this->_templatesPath = $path;
  }


  protected function calculateTemplate($class = null)
  {
    $reflector = new \ReflectionClass($class === null ? \get_class($this) : $class);
    $ns        = ltrim($reflector->getName(), "\\");
    $nsParts  = explode('\\', $ns);

    foreach($nsParts as $part)
    {
      if($class === null)
      {
        \array_shift($nsParts);
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
        \array_shift($nsParts);
      }
    }

    $this->_templatesPath = dirname($reflector->getFileName());
    for($ii = 0; $ii < count($nsParts); $ii++)
    {
      $this->_templatesPath = dirname($this->_templatesPath);
    }

    $this->setTemplateFile(strtolower(implode('\\', $nsParts)));

    return $this;
  }

  protected function setTemplateFile($template)
  {
    $this->_templateFile = $template;

    return $this;
  }

  public function render()
  {
    if($this->_templateFile === null)
    {
      $this->calculateTemplate();
    }

    $this->_templatesPath = rtrim($this->_templatesPath, '/\\') . DIRECTORY_SEPARATOR;

    $this->_template = new Template($this->_templateFile, $this->_templatesPath . 'templates');
    $this->_template->setDispatcher($this);
    foreach($this as $k => $v)
    {
      $this->_template->setData($k, $v);
    }

    return $this->_template->render();
  }
}
