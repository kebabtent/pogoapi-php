<?php
namespace POGOAPI\Session\Requests;

use POGOProtos\Data\Player\ContactSettings;
use POGOProtos\Networking\Requests\Messages\SetContactSettingsMessage;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Responses\SetContactSettingsResponse;

/**
 * @method SetContactSettingsResponse getResponse()
 */
class ContactSettingsRequest extends Request {
  /**
   * @return RequestType
   */
  public function getType() {
    return RequestType::SET_CONTACT_SETTINGS();
  }

  /**
   * @return SetContactSettingsMessage
   */
  public function getRequestMessage() {
    $msg = new SetContactSettingsMessage();

    $contact = $this->session->getProfile()->getContact();

    $contactSettings = new ContactSettings();
    $contactSettings->setSendMarketingEmails($contact->getSendMarketingEmails());
    $contactSettings->setSendPushNotifications($contact->getSendPushNotifications());

    $msg->setContactSettings($contactSettings);

    return $msg;
  }

  /**
   * @param string $raw
   * @return SetContactSettingsResponse
   */
  protected function getResponseHandler($raw) {
    return new SetContactSettingsResponse($raw);
  }
}
