<?php
/**
 * User: brooke.bryan
 * Date: 26/10/12
 * Time: 12:22
 * Description: Quick HTML templates
 */

namespace Cubex\View;

class Partial implements Renderable
{

  private $_template;
  private $_variables;
  private $_elements;
  private $_element_data = array();
  protected $_glue = '';

  /**
   * @param string $template  (HTML Template)
   * @param null   $variables (array of variables e.g. array("name","description");
   */
  public function __construct($template = '', $variables = null)
  {
    $this->_template  = $template;
    $this->_variables = $variables === null ? array() : $variables;
    $this->clearElements();
  }

  /**
   * Add element, args used in same order as defined in the constructor
   */
  public function addElement(/*$element,$element,...*/)
  {
    $this->addItem(\func_get_args());

    return $this;
  }

  private function addItem($args)
  {
    $element               = $this->_template;
    $this->_element_data[] = $args; //Allow for changing the template at a later point in time, or handling in render

    foreach($this->_variables as $arg => $key)
    {
      $element = \str_replace('{#' . $key . '}', $args[$arg], $element);
    }

    $this->_elements[] = \vsprintf($element, array_map(array('\Cubex\View\HTMLElement', 'escape'), $args));
  }

  public function addElements(array $elements)
  {
    foreach($elements as $element)
    {
      $this->addItem($element);
    }

    return $this;
  }

  public function setGlue($glue = '')
  {
    $this->_glue = $glue;

    return $this;
  }

  /**
   * @return string Rendered elements
   */
  public function render()
  {
    return \implode($this->_glue === null ? '' : $this->_glue, $this->_elements);
  }

  public function __toString()
  {
    return $this->render();
  }

  /**
   * Clear all elements added
   */
  public function clearElements()
  {
    $this->_elements = array();
  }

  public static function Single($template/*$element,$element,...*/)
  {
    $partial = new Partial($template);
    $args = \func_get_args();
    \array_shift($args);
    $partial->addItem($args);
    return $partial;
  }
}
