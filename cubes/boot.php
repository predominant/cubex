<?php
/**
 * User: Brooke Bryan
 * Date: 14/11/12
 * Time: 19:16
 * Description: Cubex Boot
 */


/**
 * Identity function, returns its argument unmodified.
 * @param   item.
 * @return  mixed Unmodified argument.
 */
function id($o)
{
  return $o;
}

/* Translation functions */

/**
 * Translate string to locale, wrapper for gettext
 *
 * @link http://php.net/manual/en/function.gettext.php
 * @param string $string
 * @return string
 */
function t($string)
{
  return _($string);
}

/**
 * Translate plural, wrapper for ngettext
 *
 * @link http://php.net/manual/en/function.ngettext.php
 * @param      $singular
 * @param null $plural
 * @param int  $number
 * @return string
 */
function p($singular, $plural = null, $number = 0)
{
  return ngettext($singular, $plural, $number);
}

/**
 * Translate string using specific domain
 *
 * @link http://php.net/manual/en/function.dgettext.php
 * @param $domain
 * @param $string
 * @return string
 */
function dt($domain, $string)
{
  return dgettext($domain, $string);
}

/**
 * Translate plural, using specific domain
 *
 * @link http://php.net/manual/en/function.dngettext.php
 * @param      $domain
 * @param      $singular
 * @param null $plural
 * @param int  $number
 * @return string
 */
function dp($domain, $singular, $plural = null, $number = 0)
{
  return dngettext($domain, $singular, $plural, $number);
}

/**
 * Bind domain to language file (CUBEX_ROOT/locale)
 *
 * @link http://php.net/manual/en/function.bindtextdomain.php
 * @param $domain
 * @return string
 */
function btdom($domain)
{
  return bindtextdomain($domain, CUBEX_ROOT . "/locale");
}

/**
 * Set the text domain, wrapper for textdomain
 *
 * @link http://php.net/manual/en/function.textdomain.php
 * @param string     $domain
 * @param bool       $bind
 * @return string
 */
function tdom($domain, $bind = false)
{
  if($bind) btdom($domain);

  return textdomain($domain);
}

require_once('base/cubex.php');
