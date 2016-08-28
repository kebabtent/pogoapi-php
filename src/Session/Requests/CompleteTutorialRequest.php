<?php
namespace POGOAPI\Session\Requests;

use POGOAPI\Session\Session;
use POGOProtos\Enums\TutorialState;
use POGOProtos\Networking\Requests\Messages\MarkTutorialCompleteMessage;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Responses\MarkTutorialCompleteResponse;

/**
 * @method MarkTutorialCompleteResponse getResponse()
 */
class CompleteTutorialRequest extends Request {
  protected $completed;

  /**
   * @param Session $session
   */
  public function __construct(Session $session) {
    parent::__construct($session);
    $this->completed = [];
  }

  /**
   * @param TutorialState $tutorial
   * @return bool
   */
  protected function tutorialCompleted(TutorialState $tutorial) {
    return in_array($tutorial, $this->completed);
  }

  /**
   * @param TutorialState $tutorial
   */
  public function completeTutorial(TutorialState $tutorial) {
    if (!$this->tutorialCompleted($tutorial)) {
      $this->completed[] = $tutorial;
    }
  }

  /**
   * @param array $tutorials
   */
  public function completeTutorials($tutorials) {
    foreach ($tutorials as $tutorial) {
      if ($tutorial instanceof TutorialState) {
        $this->completeTutorial($tutorial);
      }
    }
  }

  /**
   * @return RequestType
   */
  public function getType() {
    return RequestType::MARK_TUTORIAL_COMPLETE();
  }

  /**
   * @return MarkTutorialCompleteMessage
   */
  public function getRequestMessage() {
    $msg = new MarkTutorialCompleteMessage();

    $contact = $this->session->getProfile()->getContact();

    $msg->setSendMarketingEmails($contact->getSendMarketingEmails());
    $msg->setSendPushNotifications($contact->getSendPushNotifications());
    foreach ($this->completed as $state) {
      /** @var TutorialState $state */
      $msg->addTutorialsCompleted($state);
    }

    return $msg;
  }

  /**
   * @param string $raw
   * @return MarkTutorialCompleteResponse
   */
  protected function getResponseHandler($raw) {
    return new MarkTutorialCompleteResponse($raw);
  }
}
