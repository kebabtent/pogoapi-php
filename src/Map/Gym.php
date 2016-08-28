<?php
namespace POGOAPI\Map;

use InvalidArgumentException;
use POGOProtos\Map\Fort\FortData;
use POGOProtos\Map\Fort\FortType;
use POGOAPI\Session\Session;

class Gym extends Fort {

  /**
   * @param Session $session
   * @param FortData $data
   */
  public function __construct(Session $session, FortData $data) {
    if (!is_null($data->getType()) && $data->getType() != FortType::GYM()) {
      throw new InvalidArgumentException("Fort not a gym");
    }

    parent::__construct($session, $data);

    // TODO: team color
    // TODO: highest cp pokemon
    // TODO: xp
    // TODO: in battle
  }
}