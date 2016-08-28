<?php
namespace POGOAPI\Session\Requests;

use POGOProtos\Networking\Requests\Messages\GetHatchedEggsMessage;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Responses\GetHatchedEggsResponse;

/**
 * @method GetHatchedEggsResponse getResponse()
 */
class HatchedEggsRequest extends Request {
  /**
   * @return RequestType
   */
  public function getType() {
    return RequestType::GET_HATCHED_EGGS();
  }

  /**
   * @return GetHatchedEggsMessage
   */
  public function getRequestMessage() {
    $msg = new GetHatchedEggsMessage();

    return $msg;
  }

  protected function getResponseHandler($raw) {
    return new GetHatchedEggsResponse($raw);
  }
}
