<?php
/**
 * User: brooke.bryan
 * Date: 28/10/12
 * Time: 01:02
 * Description:
 */
namespace Cubex\Cli;

class Shell
{

  private static $_foreground_colour;
  private static $_background_colour;

  private static $_foreground_colours = array(
    'black'        => '0;30',
    'dark_gray'    => '1;30',
    'blue'         => '0;34',
    'light_blue'   => '1;34',
    'green'        => '0;32',
    'light_green'  => '1;32',
    'cyan'         => '0;36',
    'light_cyan'   => '1;36',
    'red'          => '0;31',
    'light_red'    => '1;31',
    'purple'       => '0;35',
    'light_purple' => '1;35',
    'brown'        => '0;33',
    'yellow'       => '1;33',
    'light_gray'   => '0;37',
    'white'        => '1;37'
  );

  private static $_background_colours = array(
    'black'      => '40',
    'red'        => '41',
    'green'      => '42',
    'yellow'     => '43',
    'blue'       => '44',
    'magenta'    => '45',
    'cyan'       => '46',
    'light_gray' => '47'
  );

  public static function setForeground($colour='white')
  {
    self::$_foreground_colour = $colour;
  }

  public static function setBackground($colour='black')
  {
    self::$_background_colour = $colour;
  }

  public static function clearForeground()
  {
    self::$_foreground_colour = null;
  }

  public static function clearBackground()
  {
    self::$_background_colour = null;
  }

  public static function colouredText($string)
  {
    $colour_string = '';

    if(self::$_foreground_colour !== null)
    {
      $colour_string .= "\033[" . self::$_foreground_colours[self::$_foreground_colour] . "m";
    }

    if(self::$_background_colour !== null)
    {
      $colour_string .= "\033[" . self::$_background_colours[self::$_background_colour] . "m";
    }

    return $colour_string . $string . "\033[0m";
  }

  public static function colourText($string,$foreground=null,$background=null)
  {
    $existing_foreground = self::$_foreground_colour;
    $existing_background = self::$_background_colour;

    self::clearBackground();
    self::clearForeground();

    self::setForeground($foreground);
    self::setBackground($background);

    $coloured = self::colouredText($string);

    self::setForeground($existing_foreground);
    self::setBackground($existing_background);

    return $coloured;
  }

  /**
   * Returns the number of columns the current shell has for display.
   *
   * @return int  The number of columns.
   */
  public static function columns()
  {
    return exec('/usr/bin/env tput cols');
  }

  /**
   * Checks whether the output of the current script is a TTY or a pipe / redirect
   *
   * @return bool Output being piped
   */
  public static function isPiped()
  {
    return (function_exists('posix_isatty') && !posix_isatty(STDOUT));
  }

  /**
   * Clear screen
   */
  public static function clear()
  {
    passthru("clear");
  }
}

