<?php
/**
 * User: brooke.bryan
 * Date: 12/12/12
 * Time: 20:12
 * Description:
 */

namespace Cubex\View;

use Cubex\Cubex;

/**
 * Rendered via phtml
 */
class TemplatedView extends View
{

  protected $_renderFile = null;
  protected $_renderFolder = null;

  /**
   * Set template file (excluding .phtml)
   *
   * @param $path
   */
  public function setTemplateFile($path)
  {
    $this->_renderFile = $path;
  }

  /**
   * Configure a specific template base path
   *
   * @param $path
   */
  protected function setTemplatesPath($path)
  {
    $this->_renderFolder = $path;
  }


  /**
   * Build template path and file
   *
   * @param null $class
   *
   * @return TemplatedView
   */
  private function _calculateTemplate($class = null)
  {
    $reflector = new \ReflectionClass($class === null ? \get_class($this) : $class);
    $ns        = ltrim($reflector->getName(), "\\");
    $nsParts   = explode('\\', $ns);

    foreach($nsParts as $part)
    {
      if($class === null)
      {
        \array_shift($nsParts);
      }

      $part = \strtolower($part);
      if(\in_array($part, array('controllers', 'views')) || \substr($part, -10) == 'controller')
      {
        break;
      }

      if($class !== null)
      {
        \array_shift($nsParts);
      }
    }

    $templatesPath = dirname($reflector->getFileName());
    $partCount     = count($nsParts);
    for($ii = 0; $ii < $partCount; $ii++)
    {
      $templatesPath = dirname($templatesPath);
    }

    if($this->_renderFolder === null)
    {
      $this->setTemplatesPath($templatesPath . DIRECTORY_SEPARATOR . 'templates');
    }

    if($this->_renderFile === null)
    {
      $this->setTemplateFile(strtolower(implode('\\', $nsParts)));
    }

    return $this;
  }


  /**
   * Render the template file
   *
   * @return string
   */
  public function render()
  {
    $this->_calculateTemplate();
    $rendered = '';

    if($this->_renderFile !== null)
    {
      $viewContent = $this->loadRaw();
      \ob_start();
      try //Make sure the view does not cause the entire render to fail
      {
        /* Close PHP tags to allow for html and opening tags */
        eval('?>' . $viewContent);
      }
      catch(\Exception $e)
      {
        \ob_get_clean();
      }

      $rendered = \ob_get_clean();
    }

    return $rendered;
  }

  /**
   * @return string File Data
   */
  protected function loadRaw()
  {
    $tmpFile = $this->_renderFolder . DIRECTORY_SEPARATOR . $this->_renderFile . '.phtml';
    return \file_get_contents($tmpFile);
  }
}
