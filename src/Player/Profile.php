<?php
namespace POGOAPI\Player;

use Exception;
use InvalidArgumentException;
use POGOAPI\Session\Session;
use POGOAPI\Session\Requests\PlayerRequest;
use POGOAPI\Util\ContextSerializable;
use POGOPRotos\Data\Player\Currency;

class Profile implements ContextSerializable {
  protected $logger;
  protected $session;

  protected $updated;

  protected $creationTimeStampMs;
  protected $username;
//  protected $team;
  protected $tutorial;
//  protected $avatar;
  protected $maxPokemonStorage;
  protected $maxItemStorage;
//  protected $dailyBonus;
//  protected $equippedBadge;
  protected $contact;
  protected $pokecoins;
  protected $stardust;
//  protected $claims;
  protected $avatar;

  /**
   * @param Session $session
   */
  public function __construct(Session $session) {
    $this->session = $session;
    $this->logger = $this->session->getLogger();

    $this->updated = -1;
  }

  /**
   * @return Session
   */
  public function getSession() {
    return $this->session;
  }

  /**
   * @return bool
   */
  public function hasUpdated() {
    return $this->updated > 0;
  }

  /**
   * @return int
   */
  public function getUpdated() {
    return $this->updated;
  }

  /**
   * @param int $time
   */
  protected function setUpdated($time = -1) {
    $this->updated = $time < 0 ? time() : $time;
  }

  /**
   * @return int
   */
  public function timeSinceUpdated() {
    return $this->hasUpdated() ? time()-$this->updated : -1;
  }

  /**
   * @return int
   */
  public function getCreationTimestampMs() {
    return $this->creationTimeStampMs;
  }

  /**
   * @param int $creationTimeStampMs
   */
  protected function setCreationTimeStampMs($creationTimeStampMs) {
    $this->creationTimeStampMs = $creationTimeStampMs;
  }

  /**
   * @return string
   */
  public function getUsername() {
    return $this->username;
  }

  /**
   * @param string $username
   */
  protected function setUsername($username) {
    $this->username = $username;
  }

  /**
   * @return bool
   */
  public function hasTutorial() {
    return !is_null($this->tutorial);
  }

  /**
   * @return Tutorial
   */
  public function getTutorial() {
    if (!$this->hasTutorial()) {
      $this->tutorial = new Tutorial($this);
    }
    return $this->tutorial;
  }

  /**
   * @param Tutorial $tutorial
   */
  protected function setTutorial(Tutorial $tutorial) {
    $this->tutorial = $tutorial;
  }

  /**
   * @return int
   */
  public function getMaxPokemonStorage() {
    return $this->maxPokemonStorage;
  }

  /**
   * @param int $maxPokemonStorage
   */
  protected function setMaxPokemonStorage($maxPokemonStorage) {
    $this->maxPokemonStorage = $maxPokemonStorage;
  }

  /**
   * @return int
   */
  public function getMaxItemStorage() {
    return $this->maxItemStorage;
  }

  /**
   * @param int $maxItemStorage
   */
  protected function setMaxItemStorage($maxItemStorage) {
    $this->maxItemStorage = $maxItemStorage;
  }

  /**
   * @return bool
   */
  public function hasContact() {
    return !is_null($this->contact);
  }

  /**
   * @return Contact
   */
  public function getContact() {
    if (!$this->hasContact()) {
      $this->contact = new Contact($this);
    }
    return $this->contact;
  }

  /**
   * @param Contact $contact
   */
  public function setContact(Contact $contact) {
    $this->contact = $contact;
  }

  /**
   * @return int
   */
  public function getPokecoins() {
    return $this->pokecoins;
  }

  /**
   * @param int $pokecoins
   */
  protected function setPokecoins($pokecoins) {
    $this->pokecoins = $pokecoins;
  }

  /**
   * @return int
   */
  public function getStardust() {
    return $this->stardust;
  }

  /**
   * @param int $stardust
   */
  protected function setStardust($stardust) {
    $this->stardust = $stardust;
  }

  /**
   * @return bool
   */
  public function hasAvatar() {
    return !is_null($this->avatar);
  }

  /**
   * @return Avatar
   */
  public function getAvatar() {
    return $this->avatar;
  }

  /**
   * @param Avatar $avatar
   */
  public function setAvatar(Avatar $avatar) {
    $this->avatar = $avatar;
  }

  /**
   * @throws Exception
   */
  public function update() {
    $this->logger->debug("Update profile");

    $req = new PlayerRequest($this->session);
    $req->execute();
    $resp = $req->getResponse();
    if (!$resp->hasPlayerData()) {
      throw new Exception("Unable to obtain player data");
    }
    $data = $resp->getPlayerData();

    $this->setUpdated();
    $this->setCreationTimeStampMs($data->getCreationTimestampMs());
    $this->setUsername($data->getUsername());
    if ($data->hasTutorialStateList()) {
      $this->getTutorial()->updated($data->getTutorialStateList());
    }
    else {
      $this->getTutorial()->clear();
    }
    $this->setMaxPokemonStorage($data->getMaxPokemonStorage());
    $this->setMaxItemStorage($data->getMaxItemStorage());
    $this->getContact()->updated($data->getContactSettings());
    if ($data->hasAvatar()) {
      if (!$this->hasAvatar()) {
        $this->setAvatar(Avatar::generateRandom($this));
      }
      $this->getAvatar()->updated($data->getAvatar());
    }

    $this->pokecoins = 0;
    $this->stardust = 0;
    foreach ($data->getCurrenciesList() as $currency) {
      /* @var Currency $currency */
      $amount = $currency->getAmount();
      if (is_null($amount)) {
        continue;
      }

      switch ($currency->getName()) {
        case "POKECOIN":
          $this->pokecoins = $amount;
          break;
        case "STARDUST":
          $this->stardust = $amount;
          break;
        default:
          break;
      }
    }
  }

  /**
   * @return string
   */
  public function serialize() {
    if (!$this->hasUpdated()) {
      return [];
    }

    return [
      "updated" => $this->getUpdated(),
      "creation_timestamp_ms" => $this->getCreationTimestampMs(),
      "username" => $this->getUsername(),
      "tutorial" => $this->hasTutorial() ? $this->getTutorial()->serialize() : [],
      "max_pokemon_storage" => $this->getMaxPokemonStorage(),
      "max_item_storage" => $this->getMaxItemStorage(),
      "contact" => $this->hasContact() ? $this->getContact()->serialize() : [],
      "pokecoins" => $this->getPokecoins(),
      "stardust" => $this->getStardust(),
      "avatar" => $this->hasAvatar() ? $this->getAvatar()->serialize() : null
    ];
  }

  /**
   * @param Session $session
   * @param array $data
   * @return Profile
   * @throws InvalidArgumentException
   */
  public static function unserialize($session, $data) {
    if (!($session instanceof Session)) {
      throw new InvalidArgumentException("Expected Session instance");
    }

    $profile = new Profile($session);
    if (isset($data['updated']) && $data['updated'] > 0) {
      $profile->setUpdated($data['updated']);
      $profile->setCreationTimeStampMs($data['creation_timestamp_ms']);
      $profile->setUsername($data['username']);
      $profile->setTutorial(Tutorial::unserialize($profile, $data['tutorial']));
      $profile->setMaxPokemonStorage($data['max_pokemon_storage']);
      $profile->setMaxItemStorage($data['max_item_storage']);
      $profile->setContact(Contact::unserialize($profile, $data['contact']));
      $profile->setPokecoins($data['pokecoins']);
      $profile->setStardust($data['stardust']);
      if (isset($data['avatar']) && !is_null($data['avatar']) && strlen($data['avatar']) > 1) {
        $profile->setAvatar(Avatar::unserialize($profile, $data['avatar']));
      }
    }

    return $profile;
  }
}
