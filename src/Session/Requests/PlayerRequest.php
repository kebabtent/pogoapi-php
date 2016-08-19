<?php
namespace POGOAPI\Session\Requests;

use POGOAPI\Session\Session;
use POGOProtos\Networking\Requests\Messages\GetHatchedEggsMessage;
use POGOProtos\Networking\Requests\RequestType;
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
   * @return GetHatchedEggsMessage
   */
  public function getRequestMessage() {
    $msg = new GetHatchedEggsMessage();
    return $msg;
  }

  /**
   * @param string $raw
   * @return GetPlayerResponse
   */
  public function getResponseHandler($raw) {
    return new GetPlayerResponse($raw);
  }
}
