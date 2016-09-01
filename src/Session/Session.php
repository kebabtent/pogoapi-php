<?php
namespace POGOAPI\Session;

use Exception;
use Monolog\Logger;
use POGOAPI\Session\Requests\MapObjectsRequest;
use POGOAPI\Util\MicroTime;
use POGOAPI\Player\Profile;
use POGOAPI\Map\Location;
use POGOAPI\Session\Requests\PlayerRequest;

abstract class Session {
  const APPVERSION = "1.3.1";

  protected $logger;
  protected $handler;
  protected $location;

  protected $authTicket;
  protected $endpoint;
  protected $startMicroTime;
  protected $sessionHash;

  protected $profile;

  /**
   * @param Logger $logger
   * @param Location $location
   */
  public function __construct(Logger $logger, Location $location) {
    $this->logger = $logger;
    $this->location = $location;
    $this->handler = new RequestHandler($this);
  }

  /**
   * @return Logger
   */
  public function getLogger() {
    return $this->logger;
  }

  /**
   * @return RequestHandler
   */
  public function getRequestHandler() {
    return $this->handler;
  }

  public function hasProfile() {
    return !is_null($this->profile);
  }

  /**
   * @return Profile
   */
  public function getProfile() {
    if (!$this->hasProfile()) {
      $this->profile = new Profile($this);
    }
    return $this->profile;
  }

  /**
   * @param Profile $profile
   */
  public function setProfile(Profile $profile) {
    $this->profile = $profile;
  }

  /**
   * @return Location
   */
  public function getLocation()  {
    return $this->location;
  }

  /**
   * @return bool
   */
  public function hasAuthTicket()  {
    return !is_null($this->authTicket);
  }

  /**
   * @return AuthTicket
   * @throws Exception
   */
  public function getAuthTicket() {
    if (!$this->hasAuthTicket()) {
      throw new Exception("No auth ticket");
    }
    return $this->authTicket;
  }

  /**
   * @param AuthTicket $authTicket
   */
  public function setAuthTicket(AuthTicket $authTicket) {
    $this->authTicket = $authTicket;
  }

  /**
   * @return bool
   */
  public function hasEndpoint() {
    return !is_null($this->endpoint);
  }

  /**
   * @return string
   */
  public function getEndpoint() {
    return $this->endpoint;
  }

  /**
   * @param string $endpoint
   */
  public function setEndpoint($endpoint) {
    $this->endpoint = $endpoint;
  }

  /**
   * @return int
   */
  public function getStartMicroTime() {
    if (is_null($this->startMicroTime)) {
      $this->start();
    }

    return $this->startMicroTime;
  }

  /**
   * @param int $microTime
   */
  public function setStartMicroTime($microTime) {
    $this->startMicroTime = $microTime;
  }

  /**
   * @return string
   */
  public function getSessionHash() {
    if (is_null($this->sessionHash)) {
      $this->start();
    }

    return $this->sessionHash;
  }

  /**
   * @param string $hash
   */
  public function setSessionHash($hash) {
    $this->sessionHash = $hash;
  }

  public function start() {
    $this->startMicroTime = MicroTime::get();
    $this->sessionHash = random_bytes(16);
  }

  /**
   * @param Location $location
   */
  public function setLocation(Location $location) {
    $this->location = $location;
  }

  /**
   * @return MapObjectsRequest
   * @throws Exception
   */
  public function getMapObjects() {
    $req = new MapObjectsRequest($this);
    $req->execute();
    return $req;
  }

  abstract public function authenticate();

  /**
   * @return AccountType
   */
  abstract public function getType();

  /**
   * @return bool
   */
  abstract public function hasToken();

  /**
   * @return string
   */
  abstract public function getToken();

  /**
   * @param string $token
   */
  abstract public function setToken($token);

  /**
   * @throws Exception
   */
  public function createEndpoint() {
    $this->start();

    $req = new PlayerRequest($this);
    $this->handler->execute([$req], false, true);
  }
}
