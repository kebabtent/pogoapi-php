<?php
namespace POGOAPI\Util;

interface ContextSerializable {
  /**
   * @return mixed
   */
  public function serialize();

  /**
   * @param mixed $context
   * @param mixed $data
   * @return self
   */
  public static function unserialize($context, $data);
}
