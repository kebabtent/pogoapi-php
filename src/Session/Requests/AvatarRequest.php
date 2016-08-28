<?php
namespace POGOAPI\Session\Requests;

use POGOProtos\Networking\Requests\Messages\SetAvatarMessage;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Responses\SetAvatarResponse;
use Exception;

/**
 * @method SetAvatarResponse getResponse()
 */
class AvatarRequest extends Request {
  /**
   * @return RequestType
   */
  public function getType() {
    return RequestType::SET_AVATAR();
  }

  /**
   * @return SetAvatarMessage
   * @throws Exception
   */
  public function getRequestMessage() {
    $msg = new SetAvatarMessage();
    $profile = $this->session->getProfile();
    if (!$profile->hasAvatar()) {
      throw new Exception("No avatar set");
    }
    $msg->setPlayerAvatar($profile->getAvatar()->toProto());
    return $msg;
  }

  /**
   * @param string $raw
   * @return SetAvatarResponse
   */
  protected function getResponseHandler($raw) {
    return new SetAvatarResponse($raw);
  }
}