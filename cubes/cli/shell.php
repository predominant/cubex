<?php
/**
 * User: brooke.bryan
 * Date: 28/10/12
 * Time: 01:02
 * Description:
 */
namespace Cubex\Cli;

/**
 * Basic Shell improvements
 */
class Shell
{
  const COLOUR_FOREGROUND_BLACK        = '0;30';
  const COLOUR_FOREGROUND_DARK_GREY    = '1;30';
  const COLOUR_FOREGROUND_BLUE         = '0;34';
  const COLOUR_FOREGROUND_LIGHT_BLUE   = '1;34';
  const COLOUR_FOREGROUND_GREEN        = '0;32';
  const COLOUR_FOREGROUND_LIGHT_GREEN  = '1;32';
  const COLOUR_FOREGROUND_CYAN         = '0;36';
  const COLOUR_FOREGROUND_LIGHT_CYAN   = '1;36';
  const COLOUR_FOREGROUND_RED          = '0;31';
  const COLOUR_FOREGROUND_LIGHT_RED    = '1;31';
  const COLOUR_FOREGROUND_PURPLE       = '0;35';
  const COLOUR_FOREGROUND_LIGHT_PURPLE = '1;35';
  const COLOUR_FOREGROUND_BROWN        = '0;33';
  const COLOUR_FOREGROUND_YELLOW       = '1;33';
  const COLOUR_FOREGROUND_LIGHT_GREY   = '0;37';
  const COLOUR_FOREGROUND_WHITE        = '1;37';

  const COLOUR_BACKGROUND_BLACK      = '40';
  const COLOUR_BACKGROUND_RED        = '41';
  const COLOUR_BACKGROUND_GREEN     = '42';
  const COLOUR_BACKGROUND_YELLOW     = '43';
  const COLOUR_BACKGROUND_BLUE       = '44';
  const COLOUR_BACKGROUND_MAGENTA    = '45';
  const COLOUR_BACKGROUND_CYAN       = '46';
  const COLOUR_BACKGROUND_LIGHT_GREY = '47';

  private static $_foregroundColour;
  private static $_backgroundColour;

  /**
   * Set forground colour for all shell output
   *
   * @param string $colour
   */
  public static function setForeground($colour = self::COLOUR_FOREGROUND_WHITE)
  {
    self::$_foregroundColour = $colour;
  }

  /**
   * Set background colour for all shell output
   *
   * @param string $colour
   */
  public static function setBackground($colour = self::COLOUR_BACKGROUND_BLACK)
  {
    self::$_backgroundColour = $colour;
  }

  public static function clearForeground()
  {
    self::$_foregroundColour = null;
  }

  public static function clearBackground()
  {
    self::$_backgroundColour = null;
  }

  /**
   * output coloured text based on the defined colours
   *
   * @param $string
   *
   * @return string
   */
  public static function colouredText($string)
  {
    $colourString = '';

    if(self::$_foregroundColour !== null)
    {
      $colourString .= "\033[" . self::$_foregroundColour . "m";
    }

    if(self::$_backgroundColour !== null)
    {
      $colourString .= "\033[" . self::$_backgroundColour . "m";
    }

    return $colourString . $string . "\033[0m";
  }

  /**
   * Output specific coloured text
   *
   * @param      $string
   * @param null $foreground
   * @param null $background
   *
   * @return string
   */
  public static function colourText($string, $foreground = null, $background = null)
  {
    $existingForeground = self::$_foregroundColour;
    $existingBackground = self::$_backgroundColour;

    self::clearBackground();
    self::clearForeground();

    self::setForeground($foreground);
    self::setBackground($background);

    $coloured = self::colouredText($string);

    self::setForeground($existingForeground);
    self::setBackground($existingBackground);

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

