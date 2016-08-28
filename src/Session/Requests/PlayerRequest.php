<?php
namespace POGOAPI\Session\Requests;

use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Requests\Messages\GetPlayerMessage;
use POGOProtos\Networking\Responses\GetPlayerResponse;

/**
 * @method GetPlayerResponse getResponse()
 */
class PlayerRequest extends Request {
  /**
   * @return RequestType
   */
  public function getType() {
    return RequestType::GET_PLAYER();
  }

  /**
   * @return GetPlayerMessage
   */
  public function getRequestMessage() {
    $msg = new GetPlayerMessage();
//    $msg->setAppVersion(Session::APPVERSION);
    return $msg;
  }

  /**
   * @param string $raw
   * @return GetPlayerResponse
   */
  protected function getResponseHandler($raw) {
    return new GetPlayerResponse($raw);
  }
}
