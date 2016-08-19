<?php
namespace POGOAPI\Session\Requests;

use POGOAPI\Session\Session;
use POGOProtos\Enums\TutorialState;
use POGOProtos\Networking\Requests\Messages\MarkTutorialCompleteMessage;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Responses\MarkTutorialCompleteResponse;

class CompleteTutorialRequest extends Request {
  protected $sendMarketingEmails;
  protected $completed;

  public function __construct(Session $session) {
    parent::__construct($session);

    $this->sendMarketingEmails = false;
    $this->completed = [];
  }

  public function tutorialCompleted(TutorialState $tutorial) : bool {
    return in_array($tutorial, $this->completed);
  }

  public function completeTutorial(TutorialState $tutorial) {
    if (!$this->tutorialCompleted($tutorial)) {
      $this->completed[] = $tutorial;
    }
  }

  public function completeTutorials(array $tutorials) {
    foreach ($tutorials as $tutorial) {
      if ($tutorial instanceof TutorialState) {
        $this->completeTutorial($tutorial);
      }
    }
  }

  public function getType() {
    return RequestType::MARK_TUTORIAL_COMPLETE();
  }

  public function acceptTOS() {
    $this->completeTutorial(TutorialState::LEGAL_SCREEN());
  }

  public function selectedAvatar() {
    $this->completeTutorial(TutorialState::AVATAR_SELECTION());
  }

  public function setSendMarketingEmails($sendMarketingEmails = false) {
    $this->sendMarketingEmails = $sendMarketingEmails;
  }

  public function getRequestMessage() {
    $msg = new MarkTutorialCompleteMessage();
    $msg->setSendMarketingEmails($this->sendMarketingEmails);
    $msg->setSendPushNotifications(false);
    return $msg;
  }

  public function getResponseHandler($raw) {
    return new MarkTutorialCompleteResponse($raw);
  }
}
