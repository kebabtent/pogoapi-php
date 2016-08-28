<?php
namespace POGOAPI\Map;

use InvalidArgumentException;
use POGOProtos\Map\Fort\FortData;
use POGOProtos\Map\Fort\FortType;
use POGOAPI\Session\Session;

class Pokestop extends Fort {
  /**
   * @param Session $session
   * @param FortData $data
   */
  public function __construct(Session $session, FortData $data) {
    if ($data->getType() != FortType::CHECKPOINT()) {
      throw new InvalidArgumentException("Fort not a pokestop");
    }

    parent::__construct($session, $data);

    // TODO: modifier
    // TODO: lure
    // TODO: sponsor (maybe)
  }
}