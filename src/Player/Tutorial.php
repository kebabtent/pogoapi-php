<?php
namespace POGOAPI\Player;

use Exception;
use InvalidArgumentException;
use POGOAPI\Session\Requests\CompleteTutorialRequest;
use Protobuf\Collection;
use POGOProtos\Enums\TutorialState;
use POGOAPI\Util\ContextSerializable;

class Tutorial implements ContextSerializable {
  protected $profile;
  protected $completed;

  public function __construct(Profile $profile) {
    $this->profile = $profile;
    $this->completed = [];
  }

  /**
   * @param Collection $collection
   */
  public function updated(Collection $collection) {
    $this->clear();
    foreach ($collection as $state) {
      if ($state instanceof TutorialState){
        $this->completed[] = $state;
      }
    }
  }

  /**
   * @param array $data
   */
  public function updateFromValues($data) {
    $this->clear();
    foreach ($data as $stateValue) {
      $this->completed(TutorialState::valueOf($stateValue));
    }
  }

  public function clear() {
    $this->completed = [];
  }

  /**
   * @param TutorialState $state
   */
  protected function completed(TutorialState $state) {
    if (!$this->isCompleted($state)) {
      $this->completed[] = $state;
    }
  }

  /**
   * @param TutorialState $state
   * @return bool
   */
  public function isCompleted(TutorialState $state) {
    return in_array($state, $this->completed);
  }

  /**
   * @param TutorialState $state
   * @throws Exception
   */
  public function complete(TutorialState $state) {
    if ($this->isCompleted($state)) {
      return;
    }

    $req = new CompleteTutorialRequest($this->profile->getSession());
    $req->completeTutorial($state);
    $req->execute();
    if (!$req->getResponse()->hasPlayerData()) {
      throw new Exception("Invalid response");
    }

    $this->updated($req->getResponse()->getPlayerData()->getTutorialStateList());
  }

  /**
   * @return bool
   */
  public function isTOSAccepted() {
    return $this->isCompleted(TutorialState::LEGAL_SCREEN());
  }

  /**
   * @return bool
   */
  public function isAvatarSelected() {
    return $this->isCompleted(TutorialState::AVATAR_SELECTION());
  }

  /**
   * @return bool
   */
  public function isAccountCreated() {
    return $this->isCompleted(TutorialState::ACCOUNT_CREATION());
  }

  /**
   * @throws Exception
   */
  public function acceptTOS() {
    $this->complete(TutorialState::LEGAL_SCREEN());
  }

  /**
   * @throws Exception
   */
  public function selectAvatar() {
    $this->complete(TutorialState::AVATAR_SELECTION());
  }

  /**
   * @return array
   */
  public function serialize() {
    $data = [];
    foreach ($this->completed as $state) {
      /* @var TutorialState $state */
      $data[] = $state->value();
    }
    return $data;
  }

  /**
   * @param Profile $profile
   * @param $data
   * @return Tutorial
   * @throws InvalidArgumentException
   */
  public static function unserialize($profile, $data) {
    if (!($profile instanceof Profile)) {
      throw new InvalidArgumentException("Expected Profile instance");
    }

    $tutorial = new self($profile);
    $tutorial->updateFromValues($data);
    return $tutorial;
  }
}