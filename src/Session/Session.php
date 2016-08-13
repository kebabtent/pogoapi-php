<?php
namespace POGOAPI\Session;

use Monolog\Logger;

use POGOAPI\Player\Profile;
use POGOAPI\Map\Location;
use POGOAPI\Session\Requests\PlayerRequest;

abstract class Session {
  protected $logger;
  protected $handler;
  protected $authTicket;
  protected $endpoint;
  protected $location;

  protected $profile;

  public function __construct(Logger $logger, Location $location) {
    $this->logger = $logger;
    $this->location = $location;
    $this->handler = new RequestHandler($this);

  }

  public function getLogger() : Logger {
    return $this->logger;
  }

  public function getRequestHandler() : RequestHandler {
    return $this->handler;
  }

  public function getProfile() : Profile {
    if (!$this->profile) {
      $this->profile = new Profile($this);
      $this->profile->update();
    }
    return $this->profile;
  }

  public function getLocation() : Location {
    return $this->location;
  }

  public function hasAuthTicket() : \bool {
    return !is_null($this->authTicket) && $this->authTicket->isValid();
  }

  public function getAuthTicket() : AuthTicket {
    return $this->authTicket;
  }

  public function setAuthTicket(AuthTicket $authTicket) {
    $this->authTicket = $authTicket;
  }

  public function hasEndpoint() : \bool {
    return $this->endpoint ? true : false;
  }

  public function getEndpoint() : \string {
    return $this->endpoint;
  }

  public function setEndpoint(\string $endpoint) {
    $this->endpoint = $endpoint;
  }

  public function setLocation(Location $location) {
    $this->location = $location;
  }

  abstract public function authenticate();
  abstract public function getProvider() : \string;
  abstract public function getToken() : \string;
  abstract public function setToken($token);

  public function createEndpoint() {
    $req = new PlayerRequest($this);
    $this->handler->execute($req, false, true);

    /*$req = new Request();
    $req->setRequestType(RequestType::GET_PLAYER);
    $env = $this->wrap([$req]);
    $respEnv = $this->req($env, $this->APIURL);
    if (!$respEnv || empty($respEnv->getApiUrl())) {
      throw new Exception("Unable to get endpoint URL");
    }
    $this->endpoint = "https://".$respEnv->getApiUrl()."/rpc";*/
  }
}
