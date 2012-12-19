<?php
/**
 * User: brooke.bryan
 * Date: 26/10/12
 * Time: 12:22
 * Description: Quick HTML templates
 */

namespace Cubex\View;

/**
 *
 */
class Partial implements Renderable
{

  /**
   * @var string
   */
  private $_template;
  /**
   * @var array|null
   */
  private $_variables;
  /**
   * @var
   */
  private $_elements;
  /**
   * @var array
   */
  private $_elementData = array();
  /**
   * @var string
   */
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
  public function addElement( /*$element,$element,...*/)
  {
    $this->addItem(\func_get_args());

    return $this;
  }

  /**
   * @param $args
   */
  private function addItem($args)
  {
    $element               = $this->_template;
    $this->_elementData[] = $args; //Allow for changing the template at a later point in time, or handling in render

    foreach($this->_variables as $arg => $key)
    {
      $element = \str_replace('{#' . $key . '}', $args[$arg], $element);
    }
    if(\is_array($args))
    {
      $this->_elements[] = \vsprintf($element, \array_map(array('\Cubex\View\HTMLElement', 'escape'), $args));
    }
    else
    {
      $this->_elements[] = \sprintf($element, HTMLElement::escape($args));
    }
  }

  /**
   * @param array $elements
   *
   * @return Partial
   */public function addElements(array $elements)
  {
    foreach($elements as $element)
    {
      $this->addItem($element);
    }

    return $this;
  }

  /**
   * @param string $glue
   * @return Partial
   */public function setGlue($glue = '')
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

  /**
   * @return string
   */public function __toString()
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

  /**
   * @param $template
   * @return Partial
   */public static function single($template /*$element,$element,...*/)
  {
    $partial = new Partial($template);
    $args    = \func_get_args();
    \array_shift($args);
    $partial->addItem($args);
    return $partial;
  }
}
