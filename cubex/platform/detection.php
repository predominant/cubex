<?php
/**
 * @author: gareth.evans
 */
namespace Cubex\Platform;

use Cubex\Cubex;

class Detection implements DetectionInterface
{
  const DETECTION_CLASS_KEY = 'platform_detection';

  private static $_detection;

  /**
   * @param DetectionInterface $detection
   */
  public function __construct(DetectionInterface $detection = null)
  {
    if($detection === null)
    {
      static::$_detection = self::loadFromConfig();
    }
    else
    {
      self::$_detection = $detection;
    }
  }

  /**
   * @return Detection
   * @throws \RuntimeException
   */
  public static function loadFromConfig()
  {
    $detectionClass = Cubex::config('project')->getStr(
      static::DETECTION_CLASS_KEY, null
    );

    if($detectionClass === null || empty($detectionClass))
    {
      throw new \RuntimeException(
        "No platform detection class is set in your config<br />\n".
        "Please set<br />\n".
        "[project]<br />\n".
        static::DETECTION_CLASS_KEY.
        "={{Prefered Platform Detection Class}}"
      );
    }

    $detection = new $detectionClass();

    if(!$detection instanceof DetectionInterface)
    {
      throw new \RuntimeException(
        "The detection class does not implement the correct interface;<br />\n".
        "\\Cubex\\Platform\\DetectionInterface"
      );
    }

    return new Detection($detection);
  }

  /**
   * @return bool
   */
  public function isMobile()
  {
    return static::$_detection->isMobile();
  }

  /**
   * @return bool
   */
  public function isTablet()
  {
    return static::$_detection->isTablet();
  }

  /**
   * @return bool
   */
  public function isDesktop()
  {
    return static::$_detection->isDesktop();
  }

  /**
   * @return bool
   */
  public function canSetUserAgent()
  {
    return static::$_detection->canSetUserAgent();
  }

  /**
   * @param $userAgent
   * @throws \BadMethodCallException
   */
  public function setUserAgent($userAgent)
  {
    if($this->canSetUserAgent())
    {
      static::$_detection->setUserAgent($userAgent);
    }
    else
    {
      throw new \BadMethodCallException(
        "This detection class does not support the setUserAgent() method"
      );
    }
  }
}
