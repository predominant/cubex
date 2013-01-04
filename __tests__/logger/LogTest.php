<?php
/**
 * File: LogTest.php
 * Date: 14/12/12
 * Time: 10:01
 * @author: gareth.evans
 */
namespace Cubex\Tests;

class Logger_LogTest extends \PHPUnit_Framework_TestCase
{
  public function testCorrectLevelPassed()
  {
    Log::info("info", 404);
    $this->assertEquals(Log::LEVEL_INFO, Log::$logArguments[0]);

    Log::success("success", 404);
    $this->assertEquals(Log::LEVEL_SUCCESS, Log::$logArguments[0]);

    Log::warning("warning", 404);
    $this->assertEquals(Log::LEVEL_WARNING, Log::$logArguments[0]);

    Log::critical("critical", 404);
    $this->assertEquals(Log::LEVEL_CRITICAL, Log::$logArguments[0]);

    Log::fatal("fatal", 404);
    $this->assertEquals(Log::LEVEL_FATAL, Log::$logArguments[0]);

    Log::parseError("parseError", Log::TYPE_GENERIC, 404);
    $this->assertEquals(Log::LEVEL_PARSE, Log::$logArguments[0]);
  }
}
