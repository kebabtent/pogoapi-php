<?php
namespace POGOAPI\Session;

use POGOAPI\Util\Enum;

/**
 * @method int getValue()
 * @method static AccountType byValue(string $value)
 * @method static AccountType GOOGLE()
 * @method static AccountType PTC()
 */
class AccountType extends Enum {
  const GOOGLE = 1;
  const PTC = 2;

  /**
   * @return string
   */
  public function getProvider() {
    switch ($this->getValue()) {
      case 1:
        return "google";
      break;
      case 2:
        return "ptc";
      break;
      default:
        return "unknown";
      break;
    }
  }
}