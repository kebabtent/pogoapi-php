<?php
namespace POGOAPI\Session\Requests;

use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Requests\Messages\DownloadItemTemplatesMessage;
use POGOProtos\Networking\Responses\DownloadItemTemplatesResponse;

/**
 * @method DownloadItemTemplatesResponse getResponse()
 */
class ItemTemplatesRequest extends Request {
  /**
   * @return RequestType
   */
  public function getType() {
    return RequestType::DOWNLOAD_ITEM_TEMPLATES();
  }

  /**
   * @return DownloadItemTemplatesMessage
   */
  public function getRequestMessage() {
    $msg = new DownloadItemTemplatesMessage();
    return $msg;
  }

  /**
   * @param string $raw
   * @return DownloadItemTemplatesResponse
   */
  protected function getResponseHandler($raw) {
    return new DownloadItemTemplatesResponse($raw);
  }
}