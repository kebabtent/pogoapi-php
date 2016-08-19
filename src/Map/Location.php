<?php
namespace POGOAPI\Map;

use POGOAPI\Util\Hex;
use POGOAPI\Util\Serializable;

class Location implements Serializable {
  protected $latitude;
  protected $longitude;
  protected $altitude;

  /**
   * @param float $latitude
   * @param float $longitude
   * @param float $altitude
   */
  public function __construct($latitude, $longitude, $altitude) {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
    $this->altitude = $altitude;
  }

  /**
   * @return float
   */
  public function getLatitude() {
    return $this->latitude;
  }

  /**
   * @return float
   */
  public function getLongitude() {
    return $this->longitude;
  }

  /**
   * @return float
   */
  public function getAltitude() {
    return $this->altitude;
  }

  /**
   * @return string
   */
  public function toBytes() {
    return Hex::d2h($this->latitude).Hex::d2h($this->longitude).Hex::d2h($this->altitude);
  }

  /**
   * @return array
   */
  public function toArray() {
    return [
      "latitude" => $this->getLatitude(),
      "longitude" => $this->getLongitude(),
      "altitude" => $this->getAltitude()
    ];
  }

  /**
   * @param $data
   * @return Location
   */
  public static function fromArray($data) {
    return new self($data['latitude'], $data['longitude'], $data['altitude']);
  }
}
