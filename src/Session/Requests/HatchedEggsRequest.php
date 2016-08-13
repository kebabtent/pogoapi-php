<?php
namespace POGOAPI\Session\Requests;

use POGOAPI\Session\Requests\Request;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Responses\GetHatchedEggsResponse;

class HatchedEggsRequest extends Request {
  public function getType() {
    return RequestType::GET_HATCHED_EGGS();
  }

  public function getResponseHandler($raw) {
    return new GetHatchedEggsResponse($raw);
  }
}
