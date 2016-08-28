<?php
namespace POGOAPI\Session\Requests;

use Exception;
use POGOAPI\Map\Gym;
use POGOAPI\Map\Pokestop;
use POGOAPI\Map\WildPokemon;
use POGOProtos\Map\Fort\FortType;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Requests\Messages\GetMapObjectsMessage;
use POGOProtos\Networking\Responses\GetMapObjectsResponse;
use POGOProtos\Map\MapObjectsStatus;
use POGOProtos\Map\MapCell;
use POGOProtos\Map\Fort\FortData;
use S2\S2LatLng;
use S2\S2CellId;

/**
 * @method GetMapObjectsResponse getResponse()
 */
class MapObjectsRequest extends Request {
  protected $pokestops;
  protected $gyms;
  protected $wildPokemons;

  /**
   * @return RequestType
   */
  public function getType() {
    return RequestType::GET_MAP_OBJECTS();
  }

  /**
   * @return GetMapObjectsMessage
   */
  public function getRequestMessage() {
    $msg = new GetMapObjectsMessage();
    $msg->setLatitude($this->session->getLocation()->getLatitude());
    $msg->setLongitude($this->session->getLocation()->getLongitude());
    $this->addCellIds($msg);
    return $msg;
  }

  /**
   * @param string $raw
   * @return GetMapObjectsResponse
   */
  protected function getResponseHandler($raw) {
    return new GetMapObjectsResponse($raw);
  }

  /**
   * @param bool $defaults
   * @throws Exception
   */
  public function execute($defaults = true) {
    $this->pokestops = [];
    $this->gyms = [];
    $this->wildPokemons = [];

    parent::execute($defaults);

    $resp = $this->getResponse();
    if ($resp->getStatus() != MapObjectsStatus::SUCCESS() || !$resp->hasMapCellsList()) {
      throw new Exception("Unable to retrieve map objects");
    }

    $mapCells = $resp->getMapCellsList();
    foreach ($mapCells as $mapCell) {
      /** @var MapCell $mapCell */
      if ($mapCell->hasFortsList()) {
        $forts = $mapCell->getFortsList();
        foreach ($forts as $fort) {
          /** @var FortData $fort */
          $fortType = $fort->getType();
          $fortId = $fort->getId();
          if (is_null($fortType) || $fortType == FortType::GYM()) {
            if (!isset($this->gyms[$fortId])) {
              $this->gyms[$fortId] = new Gym($this->session, $fort);
            }
          }
          elseif ($fortType == FortType::CHECKPOINT()) {
            if (!isset($this->pokestops[$fortId])) {
              $this->pokestops[$fortId] = new Pokestop($this->session, $fort);
            }
          }
        }
      }

      if ($mapCell->hasWildPokemonsList()) {
        $wildPokemons = $mapCell->getWildPokemonsList();
        foreach ($wildPokemons as $wildPokemon) {
          $this->wildPokemons[] = new WildPokemon($wildPokemon);
        }
      }
    }
  }

  /**
   * @return Pokestop[]
   */
  public function getPokestops() {
    return $this->pokestops;
  }

  /**
   * @return int
   */
  public function getCountPokestops() {
    return count($this->pokestops);
  }

  /**
   * @return Gym[]
   */
  public function getGyms() {
    return $this->gyms;
  }

  /**
   * @return int
   */
  public function getCountGyms() {
    return count($this->gyms);
  }

  /**
   * @return WildPokemon[]
   */
  public function getWildPokemons() {
    return $this->wildPokemons;
  }

  /**
   * @return int
   */
  public function getCountWildPokemons() {
    return count($this->wildPokemons);
  }

  /**
   * @param GetMapObjectsMessage $msg
   * @param int $width
   */
  protected function addCellIds(GetMapObjectsMessage $msg, $width = 6) {
    $latLng = S2LatLng::fromDegrees($this->session->getLocation()->getLatitude(), $this->session->getLocation()->getLongitude());
    $cellId = S2CellId::fromLatLng($latLng)->parent(15);
    $size = 2**(S2CellId::MAX_LEVEL - $cellId->level());
    $iIndex = 0;
    $jIndex = 0;
    $face = $cellId->toFaceIJOrientation($iIndex, $jIndex);
    $halfWidth = (int) floor($width / 2);
    for ($x = -$halfWidth; $x <= $halfWidth; $x++) {
      for ($y = -$halfWidth; $y <= $halfWidth; $y++) {
        $msg->addCellId(S2CellId::fromFaceIJ($face, $iIndex+$x*$size, $jIndex+$y*$size)->parent(15)->id());
      }
    }

  }
}
