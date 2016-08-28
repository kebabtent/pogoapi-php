<?php
namespace POGOAPI\Player;

use InvalidArgumentException;
use POGOAPI\Session\Requests\ContactSettingsRequest;
use POGOAPI\Util\ContextSerializable;
use POGOProtos\Data\Player\ContactSettings;

class Contact implements ContextSerializable {
  protected $profile;
  protected $sendMarketingEmails;
  protected $sendPushNotifications;

  /**
   * @param Profile $profile
   */
  public function __construct(Profile $profile) {
    $this->profile = $profile;
    $this->sendMarketingEmails = false;
    $this->sendPushNotifications = false;
  }

  /**
   * @return bool
   */
  public function getSendMarketingEmails() {
    return $this->sendMarketingEmails;
  }

  /**
   * @param bool|false $sendMarketingEmails
   */
  protected function setSendMarketingEmails($sendMarketingEmails = false) {
    $this->sendMarketingEmails = $sendMarketingEmails;
  }

  /**
   * @return bool
   */
  public function getSendPushNotifications() {
    return $this->sendPushNotifications;
  }

  /**
   * @param bool|false $sendPushNotifications
   */
  protected function setSendPushNotifications($sendPushNotifications = false) {
    $this->sendPushNotifications = $sendPushNotifications;
  }

  /**
   * @param ContactSettings $contactSettings
   */
  public function updated(ContactSettings $contactSettings) {
    $this->sendMarketingEmails = $contactSettings->getSendMarketingEmails();
    $this->sendPushNotifications = $contactSettings->getSendPushNotifications();
  }

  public function update() {
    $req = new ContactSettingsRequest($this->profile->getSession());
    $req->execute();
  }

  /**
   * @return string
   */
  public function serialize() {
    return ($this->sendMarketingEmails ? "1" : "0").($this->sendPushNotifications ? "1" : "0");
  }

  /**
   * @param Profile $profile
   * @param string $data
   * @return Contact
   * @throws InvalidArgumentException
   */
  public static function unserialize($profile, $data) {
    if (!($profile instanceof Profile)) {
      throw new InvalidArgumentException("Expected Profile instance");
    }

    $parts = str_split($data);
    if (count($parts) < 2) {
      throw new InvalidArgumentException("Invalid contact parts");
    }

    $contact = new self($profile);
    $contact->setSendMarketingEmails($parts[0] != 0);
    $contact->setSendPushNotifications($parts[1] != 0);


    return $contact;
  }
}