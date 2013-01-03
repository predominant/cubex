<?php
/**
 * User: brooke.bryan
 * Date: 03/01/13
 * Time: 11:25
 * Description:
 */
namespace Cubex\Locale;

use Cubex\ServiceManager\Service;
use Cubex\ServiceManager\ServiceConfig;

/**
 * Locale Handler
 */
class Locale implements Service
{
  protected $_locale = '';

  /**
   * @return Locale
   */
  public static function instance()
  {
    return new Locale();
  }

  /**
   * @param \Cubex\ServiceManager\ServiceConfig $config
   *
   * @return mixed|void
   */
  public function configure(ServiceConfig $config)
  {
    $this->_locale = $config->getStr('default', 'en_US');

    $loc = \explode(',', $this->_locale);
    \putenv('LC_ALL=' . $loc[0]);
    \array_unshift($loc, LC_ALL);
    \call_user_func_array('setlocale', $loc);
  }
}
