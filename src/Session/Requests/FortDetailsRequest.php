<?php
namespace POGOAPI\Session\Requests;

use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Requests\Messages\FortDetailsMessage;
use POGOProtos\Networking\Responses\FortDetailsResponse;
use POGOAPI\Session\Session;
use POGOAPI\Map\Fort;

/**
 * @method FortDetailsResponse getResponse()
 */
class FortDetailsRequest extends Request {
  protected $fort;

  /**
   * @param Session $session
   * @param Fort $fort
   */
  public function __construct(Session $session, Fort $fort) {
    parent::__construct($session);
    $this->fort = $fort;
  }

  /**
   * @return RequestType
   */
  public function getType() {
    return RequestType::FORT_DETAILS();
  }

  /**
   * @return FortDetailsMessage
   */
  public function getRequestMessage() {
    $msg = new FortDetailsMessage();
    $msg->setFortId($this->fort->getId());
    $location = $this->fort->getLocation();
    $msg->setLatitude($location->getLatitude());
    $msg->setLongitude($location->getLongitude());
    return $msg;
  }

  /**
   * @param string $raw
   * @return FortDetailsResponse
   */
  protected function getResponseHandler($raw) {
    return new FortDetailsResponse($raw);
  }
}