<?php
namespace POGOAPI\Util;

class Random {
  const MTRANDMAX = 2**31-1; // Maximum value for 32bit Mersenne twister

  /**
   * Generate a random float between $min and $max
   * @param float $min
   * @param float $max
   * @return float
   */
  public static function randomFloat($min, $max) {
    if ($max < $min) {
      $temp = $max;
      $max = $min;
      $min = $temp;
    }

    return $min+mt_rand()/self::MTRANDMAX*($max-$min);
  }
}