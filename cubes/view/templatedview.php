<?php
/**
 * User: brooke.bryan
 * Date: 12/12/12
 * Time: 20:12
 * Description:
 */

namespace Cubex\View;

use Cubex\Base\Application;

class TemplatedView extends View
{

  private $_template_file;
  /** @var Template */
  protected $_template;

  public function __construct()
  {
    $this->calculateTemplate();
  }

  protected function calculateTemplate()
  {
    $app_reflect = new \ReflectionObject(Application::$app);
    $replace     = substr($app_reflect->getName(), 0, -11) . 'Views\\';
    $reflector   = new \ReflectionClass(\get_class($this));
    $this->setTemplateFile(str_replace($replace, '', $reflector->getName()));

    return $this;
  }

  protected function setTemplateFile($template)
  {
    $this->_template_file = $template;

    return $this;
  }

  public function render()
  {
    $this->_template = new Template($this->_template_file);
    foreach($this as $k => $v)
    {
      $this->_template->setData($k, $v);
    }

    return $this->_template->render();
  }
}
