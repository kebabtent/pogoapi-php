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
  public function __construct($latitude, $longitude, $altitude = -1.) {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
    $this->altitude = $altitude < 0 ? mt_rand(1, 10) : $altitude;
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
   * @param float $angle Angle in degrees (0=north, 90=east)
   * @param float $distance Distance in meter
   * @return self
   */
  public function translate($angle, $distance) {
    $realAngle = $angle;
    if ($distance < 0) {
      $realAngle += 180;
    }
    $realAngle %= 360;
    if ($realAngle < 0) {
      $realAngle += 360;
    }
    $realDistance = abs($distance);

    $oldLat = deg2rad($this->latitude);
    $oldLong = deg2rad($this->longitude);
    $relativeDistance = $realDistance/6367000.;
    $realAngleRad = deg2rad($realAngle);

    $newLat = asin( sin($oldLat)*cos($relativeDistance) + cos($oldLat)*sin($relativeDistance)*cos($realAngleRad) );
    $newLong = $oldLong + atan2(sin($realAngleRad)*sin($relativeDistance)*cos($oldLat), cos($relativeDistance)-sin($oldLat)*sin($newLat));

    $newLatDeg = rad2deg($newLat);
    $newLongDeg = rad2deg($newLong);

    $this->latitude = $newLatDeg;
    $this->longitude = $newLongDeg;
    return $this;
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
  public function serialize() {
    return [
      "latitude" => $this->getLatitude(),
      "longitude" => $this->getLongitude(),
      "altitude" => $this->getAltitude()
    ];
  }

  /**
   * @param $data
   * @return self
   */
  public static function unserialize($data) {
    return new self($data['latitude'], $data['longitude'], $data['altitude']);
  }
}
