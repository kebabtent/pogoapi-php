<?php
namespace POGOAPI\Map;

use POGOProtos\Map\Pokemon\WildPokemon as ProtoWildPokemon;

class WildPokemon {
  protected $pokemonId;
  protected $encounterId;
  protected $spawnPointId;
  protected $timeTillHiddenMs;
  protected $location;

  /**
   * @param ProtoWildPokemon $proto
   */
  public function __construct(ProtoWildPokemon $proto) {
    $this->pokemonId = $proto->getPokemonData()->getPokemonId()->value();
    $this->encounterId = $proto->getEncounterId();
    $this->spawnPointId = $proto->getSpawnPointId();
    $this->timeTillHiddenMs = $proto->getTimeTillHiddenMs();
    $this->location = new Location($proto->getLatitude(), $proto->getLongitude());
  }

  /**
   * @return int
   */
  public function getPokemonId() {
    return $this->pokemonId;
  }

  /**
   * @return int
   */
  public function getEncounterId() {
    return $this->encounterId;
  }

  /**
   * @return string
   */
  public function getSpawnPointId() {
    return $this->spawnPointId;
  }

  /**
   * @return int
   */
  public function getTimeTillHiddenMs() {
    return $this->timeTillHiddenMs;
  }

  /**
   * @return Location
   */
  public function getLocation() {
    return $this->location;
  }
}