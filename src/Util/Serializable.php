<?php
namespace POGOAPI\Util;

interface Serializable {
  /**
   * @return mixed
   */
  public function serialize();

  /**
   * @param $data
   * @return self
   */
  public static function unserialize($data);
}
