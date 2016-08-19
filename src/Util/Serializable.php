<?php
namespace POGOAPI\Util;

interface Serializable {
  /**
   * @return array
   */
  public function toArray();


  /**
   * @param $data
   * @return self
   */
  public static function fromArray($data);
}