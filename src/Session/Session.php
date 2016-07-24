<?php
namespace POGOAPI\Session;

use GuzzleHttp\Client;
use POGOProtos\Networking\Requests\Request;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Requests\Messages\GetInventoryMessage;
use POGOProtos\Networking\Requests\Messages\DownloadSettingsMessage;
use POGOProtos\Networking\Envelopes\RequestEnvelope;
use POGOProtos\Networking\Envelopes\RequestEnvelope_AuthInfo;
use POGOProtos\Networking\Envelopes\RequestEnvelope_AuthInfo_JWT;
use POGOProtos\Networking\Envelopes\ResponseEnvelope;

abstract class Session {
  protected $RPCId;
  protected $client;
  protected $APIURL;
  protected $authTicket;
  protected $endpoint;

  public function __construct() {
    $this->RPCId = mt_rand();
    $this->client = new Client([
      "headers" => ["User-Agent" => "Niantic App"]
    ]);
    $this->APIURL = "https://pgorelease.nianticlabs.com/plfe/rpc";
    $this->authTicket = false;
    $this->endpoint = false;

    // Default location: NY central park
    $this->lat = 40.77878553364602;
    $this->long = -73.96834745844728;
    $this->alt = 35;
  }

  public function getEndpoint() {
    return $this->endpoint;
  }

  public function setEndpoint($endpoint) {
    $this->endpoint = $endpoint;
  }

  public function setLocation($lat, $long, $alt) {
    $this->lat = $lat;
    $this->long = $long;
    $this->alt = $alt;
  }

  protected function getRPCId() {
    return ++$this->RPCId;
  }

  abstract public function authenticate();
  abstract public function getProvider();
  abstract public function getToken();
  abstract public function setToken();

  protected function getDefaultRequests() {
    $reqs = [];

    $eggs = new Request();
    $eggs->setRequestType(RequestType::GET_HATCHED_EGGS);
    $reqs[] = $eggs;

    $inventory = new Request();
    $inventory->setRequestType(RequestType::GET_INVENTORY);
    $inventoryMessage = new GetInventoryMessage();
    $inventoryMessage->setLastTimestampMs(0);
    $inventory->setRequestMessage($inventoryMessage->toProtobuf());
    $reqs[] = $inventory;

    $badges = new Request();
    $badges->setRequestType(RequestType::CHECK_AWARDED_BADGES);
    $reqs[] = $badges;

    $settings = new Request();
    $settings->setRequestType(RequestType::DOWNLOAD_SETTINGS);
    $settingsMessage = new DownloadSettingsMessage();
    $settingsMessage->setHash("4a2e9bc330dae60e7b74fc85b98868ab4700802e");
    $settings->setRequestMessage($settingsMessage->toProtobuf());
    $reqs[] = $settings;

    return $reqs;
  }

  public function createEndpoint() {
    $req = new Request();
    $req->setRequestType(RequestType::GET_PLAYER);
    $env = $this->wrap([$req]);
    $respEnv = $this->req($env, $this->APIURL);
    $this->endpoint = $respEnv->getApiUrl();
  }

  protected function wrap($reqs, $defaults = true) {
    $env = new RequestEnvelope();
    $env->setStatusCode(2);
    $env->setRequestId($this->getRPCId());
    $env->setLatitude($this->lat);
    $env->setLongitude($this->long);
    $env->setAltitude($this->alt);
    if ($this->authTicket) {
      $env->setAuthTicket($this->authTicket);
    }
    else {
      $info = new RequestEnvelope_AuthInfo();
      $info->setProvider($this->getProvider());
      $token = new RequestEnvelope_AuthInfo_JWT();
      $token->setContents($this->getToken());
      $token->setUnknown2(59);
      $info->setToken($token);
      $env->setAuthInfo($info);
    }
    $env->setUnknown12(989);

    $env->addAllRequests($reqs);
    if ($defaults) {
      $env->addAllRequests($this->getDefaultRequests());
    }

    return $env;
  }

  protected function req(RequestEnvelope $env, $URL = false) {
    if (!$URL) {
      $URL = $this->endpoint;
    }
    $resp = $this->client->post($URL, ["body" => $env->toProtobuf()]);
    $respEnv = new ResponseEnvelope((string) $resp->getBody());
    $authTicket = $respEnv->getAuthTicket();
    if ($authTicket) {
      $this->authTicket = $authTicket;
    }
    return $respEnv;
  }
}
