<?php
namespace POGOAPI\Session\Requests;

use Protobuf\AbstractMessage;
use POGOAPI\Session\Session;
use POGOProtos\Networking\Requests\Request as ProtoRequest;

abstract class Request {
  protected $logger;
  protected $session;
  protected $proto;
  protected $response;

  abstract public function getType();
  abstract public function getResponseHandler($raw);

  public function __construct(Session $session) {
    $this->logger = $session->getLogger();
    $this->session = $session;
    $this->proto = new ProtoRequest();
    $this->response = false;
  }

  public function toProto() {
    $this->proto->setRequestType($this->getType());
    return $this->proto;
  }

  public function setRawResponse(string $raw) {
    $message = $this->getResponseHandler($raw);
    $this->setResponse($message);
  }

  public function setResponse(AbstractMessage $response) {
    $this->response = $response;
  }

  public function getResponse() {
    return $this->response;
  }

  public function execute($defaults = true) {
    $this->session->getRequestHandler()->execute($this, $defaults);
  }
}
