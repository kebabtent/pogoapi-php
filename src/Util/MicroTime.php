<?php
namespace POGOAPI\Util;

class MicroTime {
  /**
   * @return int
   */
  public static function get() {
    return round(microtime(true)*1000);
  }
}