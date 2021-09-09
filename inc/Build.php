<?php
class Build
{
  const USE_MINIFIED = "production";

  public static function isProduction()
  {
    global $argc, $argv;

    return (isset($argc) && ($argc === 2) && ($argv[1] === self::USE_MINIFIED));
  }
}
?>
