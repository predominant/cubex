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
  public function addElement()
  {
    $element               = $this->_template;
    $args                  = func_get_args();
    $this->_element_data[] = $args; //Allow for changing the template at a later point in time, or handling in render
    foreach($this->_variables as $arg => $key)
    {
      $element = str_replace('{#' . $key . '}', $args[$arg], $element);
    }
    $element           = vsprintf($element, $args);
    $this->_elements[] = $element;

    return $this;
  }

  public function setGlue($glue = '')
  {
    $this->_glue = $glue;
    return $this;
  }

  /**
   * @param null $glue Glue for imploding all elements
   * @return string Rendered elements
   */
  public function render()
  {
    return implode($this->_glue === null ? '' : $this->_glue, $this->_elements);
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
}
