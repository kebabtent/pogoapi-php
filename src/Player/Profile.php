<?php
namespace POGOAPI\Player;

use POGOAPI\Session\Session;
use POGOAPI\Session\Requests\PlayerRequest;
use POGOProtos\Data\PlayerData;

class Profile {
  protected $logger;
  protected $session;
  protected $data;

  /**
   * @param Session $session
   */
  public function __construct(Session $session) {
    $this->logger = $session->getLogger();
    $this->session = $session;
  }

  /**
   * @param $name
   * @param $args
   * @return mixed
   */
  public function __call($name, $args) {
    return call_user_func_array(array($this->data, $name), $args);
  }

  /**
   * @return bool
   */
  public function hasData() {
    return !is_null($this->data);
  }

  /**
   * @return PlayerData
   */
  public function getData() {
    return $this->data;
  }

  public function setData(PlayerData $data) {
    $this->data = $data;
  }

  /**
   * @return null
   */
  public function update() {
    $this->logger->debug("Update profile");

    $req = new PlayerRequest($this->session);
    $req->execute();
    $this->data = $req->getResponse()->getPlayerData();
  }

  /**
   * @return string
   */
  public function toBinary() {
    return $this->getData()->toStream()->getContents();
  }

  /**
   * @return string
   */
  public function toHex() {
    return bin2hex($this->toBinary());
  }

  /**
   * @param Session $session
   * @param string $raw
   * @return Profile
   */
  public static function fromBinary(Session $session, $raw) {
    $profile = new self($session);
    $data = new PlayerData($raw);
    $profile->setData($data);
    return $profile;
  }

  /**
   * @param Session $session
   * @param $hex
   * @return Profile
   */
  public static function fromHex(Session $session, $hex) {
    return self::fromBinary($session, hex2bin($hex));
  }
}
