<?php
namespace POGOAPI\Map;

use POGOAPI\Util\Hex;

class Location {
  protected $latitude;
  protected $longitude;
  protected $altitude;

  public function __construct(\float $latitude, \float $longitude, \float $altitude) {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
    $this->altitude = $altitude;
  }

  public function getLatitude() : \float {
    return $this->latitude;
  }

  public function getLongitude() : \float {
    return $this->longitude;
  }

  public function getAltitude() : \float {
    return $this->altitude;
  }

  public function toBytes() : \string {
    return Hex::d2h($this->latitude).Hex::d2h($this->longitude).Hex::d2h($this->altitude);
  }
}
