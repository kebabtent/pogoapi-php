<?php
namespace POGOAPI\Session\Requests;

use Exception;
use POGOProtos\Networking\Requests\RequestType;
use Protobuf\AbstractMessage;
use POGOAPI\Session\Session;
use POGOProtos\Networking\Requests\Request as ProtoRequest;

abstract class Request {
  protected $logger;
  protected $session;
  protected $response;

  /**
   * @return RequestType
   */
  abstract public function getType();

  /**
   * @return AbstractMessage
   */
  abstract public function getRequestMessage();

  /**
   * @param string $raw
   * @return AbstractMessage
   */
  abstract protected function getResponseHandler($raw);

  /**
   * @param Session $session
   */
  public function __construct(Session $session) {
    $this->logger = $session->getLogger();
    $this->session = $session;
    $this->response = false;
  }

  /**
   * @return ProtoRequest
   */
  public function toProto() {
    $proto = new ProtoRequest();
    $proto->setRequestType($this->getType());
    $proto->setRequestMessage($this->getRequestMessage()->toStream()->getContents());
    return $proto;
  }

  /**
   * @param string $raw
   */
  public function setRawResponse($raw) {
    $message = $this->getResponseHandler($raw);
    $this->setResponse($message);
  }

  /**
   * @param AbstractMessage $response
   */
  public function setResponse(AbstractMessage $response) {
    $this->response = $response;
  }

  /**
   * @return AbstractMessage
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * @param bool $defaults
   * @throws Exception
   */
  public function execute($defaults = true) {
    $this->session->getRequestHandler()->execute([$this], $defaults);
  }
}
