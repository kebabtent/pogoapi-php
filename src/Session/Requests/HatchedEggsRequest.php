<?php
namespace POGOAPI\Session\Requests;

use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Responses\GetHatchedEggsResponse;

class HatchedEggsRequest extends Request {
  /**
   * @return RequestType
   */
  public function getType() {
    return RequestType::GET_HATCHED_EGGS();
  }

  /**
   * @return GetPlayerMessage
   */
  public function getRequestMessage() {
    $msg = new GetPlayerMessage();
    $msg->setAppVersion(Session::APPVERSION);
    return $msg;
  }

  public function getResponseHandler($raw) {
    return new GetHatchedEggsResponse($raw);
  }
}
