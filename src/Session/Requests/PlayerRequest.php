<?php
namespace POGOAPI\Session\Requests;

use POGOAPI\Session\Requests\Request;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Responses\GetPlayerResponse;

class PlayerRequest extends Request {
  public function getType() {
    return RequestType::GET_PLAYER();
  }

  public function getResponseHandler($raw) {
    return new GetPlayerResponse($raw);
  }
}
