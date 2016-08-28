<?php
namespace POGOAPI\Player;

use Exception;
use InvalidArgumentException;
use POGOAPI\Session\Requests\AvatarRequest;
use POGOAPI\Util\ContextSerializable;
use POGOProtos\Data\Player\PlayerAvatar;
use POGOProtos\Enums\Gender;
use POGOProtos\Networking\Responses\SetAvatarResponse\Status;

class Avatar implements ContextSerializable {
  protected $profile;

  protected $skin;
  protected $hair;
  protected $shirt;
  protected $pants;
  protected $hat;
  protected $shoes;
  protected $gender;
  protected $eyes;
  protected $backpack;

  /**
   * @param Profile $profile
   */
  protected function __construct(Profile $profile) {
    $this->profile = $profile;
  }

  /**
   * @return int
   */
  public function getSkin() {
    return $this->skin;
  }

  /**
   * @param int $skin
   */
  protected function setSkin($skin) {
    $this->skin = $skin;
  }

  /**
   * @return int
   */
  public function getHair() {
    return $this->hair;
  }

  /**
   * @param int $hair
   */
  protected function setHair($hair) {
    $this->hair = $hair;
  }

  /**
   * @return int
   */
  public function getShirt() {
    return $this->shirt;
  }

  /**
   * @param int $shirt
   */
  protected function setShirt($shirt) {
    $this->shirt = $shirt;
  }

  /**
   * @return int
   */
  public function getPants() {
    return $this->pants;
  }

  /**
   * @param int $pants
   */
  protected function setPants($pants) {
    $this->pants = $pants;
  }

  /**
   * @return int
   */
  public function getHat() {
    return $this->hat;
  }

  /**
   * @param int $hat
   */
  protected function setHat($hat) {
    $this->hat = $hat;
  }

  /**
   * @return int
   */
  public function getShoes() {
    return $this->shoes;
  }

  /**
   * @param int $shoes
   */
  protected function setShoes($shoes) {
    $this->shoes = $shoes;
  }

  /**
   * @return int
   */
  public function getGender() {
    return $this->gender;
  }

  /**
   * @param int $gender
   */
  protected function setGender($gender) {
    $this->gender = $gender;
  }

  /**
   * @return bool
   */
  public function isMale() {
    return $this->getGender() == Gender::MALE()->value();
  }

  /**
   * @return bool
   */
  public function isFemale() {
    return !$this->isMale();
  }

  /**
   * @return int
   */
  public function getEyes() {
    return $this->eyes;
  }

  /**
   * @param int $eyes
   */
  protected function setEyes($eyes) {
    $this->eyes = $eyes;
  }

  /**
   * @return int
   */
  public function getBackpack() {
    return $this->backpack;
  }

  /**
   * @param int $backpack
   */
  protected function setBackpack($backpack) {
    $this->backpack = $backpack;
  }

  /**
   * @throws Exception
   */
  public function set() {
    $tutorial = $this->profile->getTutorial();
    if ($tutorial->isAvatarSelected()) {
      throw new Exception("Avatar already selected");
    }

    $req = new AvatarRequest($this->profile->getSession());
    $req->execute();

    var_dump($req->getResponse());

    if ($req->getResponse()->getStatus()->value() != Status::SUCCESS_VALUE) {
      throw new Exception("Unable to set avatar");
    }
  }

  /**
   * @param PlayerAvatar $playerAvatar
   */
  public function updated(PlayerAvatar $playerAvatar) {
    $this->setSkin($playerAvatar->getSkin());
    $this->setHair($playerAvatar->getHair());
    $this->setShirt($playerAvatar->getShirt());
    $this->setPants($playerAvatar->getPants());
    $this->setHat($playerAvatar->getHat());
    $this->setShoes($playerAvatar->getShoes());
    $this->setGender(!$playerAvatar->hasGender() ? Gender::MALE_VALUE : $playerAvatar->getGender()->value());
    $this->setEyes($playerAvatar->getEyes());
    $this->setBackpack($playerAvatar->getBackpack());
  }

  /**
   * @return PlayerAvatar
   */
  public function toProto() {
    $playerAvatar = new PlayerAvatar();
    $playerAvatar->setSkin($this->getSkin());
    $playerAvatar->setHair($this->getHair());
    $playerAvatar->setShirt($this->getShirt());
    $playerAvatar->setPants($this->getPants());
    $playerAvatar->setHat($this->getHat());
    $playerAvatar->setShoes($this->getShoes());
    $playerAvatar->setGender(Gender::valueOf($this->getGender()));
    $playerAvatar->setEyes($this->getEyes());
    $playerAvatar->setBackpack($this->getBackpack());
    return $playerAvatar;
  }

  /**
   * @return string
   */
  public function serialize() {
    return (string) $this->getSkin().$this->getHair().$this->getShirt().$this->getPants().$this->getHat().$this->getShoes().$this->getGender().$this->getEyes().$this->getBackpack();
  }

  /**
   * @param Profile $profile
   * @param string $data
   * @return Avatar
   */
  public static function unserialize($profile, $data) {
    if (!($profile instanceof Profile)) {
      throw new InvalidArgumentException("Expected Profile instance");
    }
    $parts = str_split($data);
    if (count($parts) != 9) {
      throw new InvalidArgumentException("Invalid avatar parts");
    }
    $avatar = new self($profile);
    $avatar->setSkin((int) $parts[0]);
    $avatar->setHair((int) $parts[1]);
    $avatar->setShirt((int) $parts[2]);
    $avatar->setPants((int) $parts[3]);
    $avatar->setHat((int) $parts[4]);
    $avatar->setShoes((int) $parts[5]);
    $avatar->setGender((int) $parts[6]);
    $avatar->setEyes((int) $parts[7]);
    $avatar->setBackpack((int) $parts[8]);
    return $avatar;
  }

  /**
   * @param Profile $profile
   * @return Avatar
   */
  public static function generateRandom(Profile $profile) {
    $avatar = new self($profile);
    $avatar->setSkin(mt_rand(0, 3));
    $avatar->setHair(mt_rand(0, 5));
    $avatar->setShirt(mt_rand(0, 3));
    $avatar->setPants(mt_rand(0, 2));
    $avatar->setHat(mt_rand(0, 4));
    $avatar->setShoes(mt_rand(0, 6));
    $avatar->setGender(mt_rand(0, 1));
    $avatar->setEyes(mt_rand(0, 4));
    $avatar->setBackpack(mt_rand(0, 5));
    return $avatar;
  }
}