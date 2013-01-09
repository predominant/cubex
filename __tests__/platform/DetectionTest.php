<?php
/**
 * @author: gareth.evans
 */
namespace Cubex\Tests;
use Cubex\Cubex;
use Cubex\Platform\Detection;

class DetectionTest extends \PHPUnit_Framework_TestCase
{
  const USER_AGENT_MOBILE = "mobile";
  const USER_AGENT_TABLET = "tablet";
  const USER_AGENT_DESKTOP = "desktop";

  private $_userAgents = array();
  private $_skip;

  /**
   * @var Detection
   */
  private $_detection;

  public function setUp()
  {
    $this->_userAgents[self::USER_AGENT_MOBILE] = array();
    $this->_userAgents[self::USER_AGENT_TABLET] = array();
    $this->_userAgents[self::USER_AGENT_DESKTOP] = array();

    $this->_userAgents[self::USER_AGENT_MOBILE][] = "Mozilla/5.0 (iPhone; U; ".
      "CPU iPhone OS 4_3_2 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, ".
      "like Gecko) Version/5.0.2 Mobile/8H7 Safari/6533.18.5";
    $this->_userAgents[self::USER_AGENT_MOBILE][] = "Mozilla/5.0 (compatible; ".
      "MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; ".
      "Lumia 800)";
    $this->_userAgents[self::USER_AGENT_TABLET][] = "Mozilla/5.0 (iPad; U; ".
      "CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like ".
      "Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10";
    $this->_userAgents[self::USER_AGENT_TABLET][] = "Mozilla/5.0 (Linux; ".
      "Android 4.1.1; Nexus 7 Build/JRO03D) AppleWebKit/535.19 (KHTML, like ".
      "Gecko) Chrome/18.0.1025.166 Safari/535.19";
    $this->_userAgents[self::USER_AGENT_DESKTOP][] = "Mozilla/5.0 (Windows NT ".
      "6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.26 ".
      "Safari/537.22";
    $this->_userAgents[self::USER_AGENT_DESKTOP][] = "Opera/9.80 (Macintosh; ".
      "Intel Mac OS X; U; en) Presto/2.2.15 Version/10.00";

    $class = Cubex::config('project')->getStr(Detection::DETECTION_CLASS_KEY);
    if($class === null || empty($class))
    {
      $this->_skip = "Platform Detection not available";
    }
    else
    {
      $this->_detection = Detection::loadFromConfig();
      if(!$this->_detection->canSetUserAgent())
      {
        $this->_skip = "Platform Detection does not support setUserAgent()";
      }
    }
  }

  private function _checkSkip()
  {
    if($this->_skip !== null)
    {
      $this->markTestSkipped($this->_skip);
    }
  }

  public function testDetectionRules()
  {
    $this->_checkSkip();

    foreach($this->_userAgents as $userAgentType => $userAgents)
    {
      foreach($userAgents as $userAgent)
      {
        $this->_detection->setUserAgent($userAgent);

        switch($userAgentType)
        {
          case self::USER_AGENT_MOBILE:
            $this->assertTrue($this->_detection->isMobile());
            $this->assertFalse($this->_detection->isTablet());
            $this->assertFalse($this->_detection->isDesktop());
            break;
          case self::USER_AGENT_TABLET:
            $this->assertTrue($this->_detection->isMobile());
            $this->assertTrue($this->_detection->isTablet());
            $this->assertFalse($this->_detection->isDesktop());
            break;
          case self::USER_AGENT_DESKTOP:
            $this->assertFalse($this->_detection->isMobile());
            $this->assertFalse($this->_detection->isTablet());
            $this->assertTrue($this->_detection->isDesktop());
            break;
        }
      }
    }
  }

  public function testBothLoadOptions()
  {
    $this->_checkSkip();

    $this->assertEquals(Detection::loadFromConfig(), new Detection());
  }

  public function testCorrectExceptionsGetThrown()
  {
    $config = Cubex::core()->config('project');
    $platformDetection = $config->getStr(Detection::DETECTION_CLASS_KEY);

    $config->setData(Detection::DETECTION_CLASS_KEY, null);
    $this->setExpectedException(
      "\\RuntimeException",
      "No platform detection class is set in your config<br />\n".
      "Please set<br />\n".
      "[project]<br />\n".
      Detection::DETECTION_CLASS_KEY.
      "={{Prefered Platform Detection Class}}"
    );
    Detection::loadFromConfig($config);

    $config->setData(Detection::DETECTION_CLASS_KEY, "random");
    $this->setExpectedException(
      "\\RuntimeException",
      "The detection class does not implement the correct interface;<br />\n".
      "\\Cubex\\Platform\\DetectionInterface"
    );
    Detection::loadFromConfig($config);

    $config->setData(Detection::DETECTION_CLASS_KEY, $platformDetection);
  }

  public function testExceptionThrownWhenSetUserAgentNotAvailable()
  {
    $mockDetection = $this->getMock(
      "\\Cubex\\Platform\\Detection\\MobileDetectMobileDetectLib"
    );
    $mockDetection->expects($this->once())
      ->method("canSetUserAgent")
      ->will($this->returnArgument(false));

    $this->setExpectedException(
      "\\BadMethodCallException",
      "This detection class does not support the setUserAgent() method"
    );

    (new Detection($mockDetection))->setUserAgent("");
  }
}
