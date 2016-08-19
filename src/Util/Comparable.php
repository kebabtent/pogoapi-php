<?php
namespace POGOAPI\Util;

interface Comparable {
  /**
   * @param $obj
   * @return bool
   */
  public function equals($obj);
}