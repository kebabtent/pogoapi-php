<?php
namespace POGOAPI\Session;

use Exception;
use GuzzleHttp\Client;

use POGOAPI\Session\Requests as R;
use POGOAPI\Session\Requests\Request;

use POGOProtos\Networking\Requests\Request as ProtoRequest;
use POGOProtos\Networking\Requests\RequestType;
use POGOProtos\Networking\Requests\Messages\GetInventoryMessage;
use POGOProtos\Networking\Requests\Messages\DownloadSettingsMessage;

use POGOProtos\Networking\Envelopes\RequestEnvelope;
use POGOProtos\Networking\Envelopes\RequestEnvelope\AuthInfo;
use POGOProtos\Networking\Envelopes\RequestEnvelope\AuthInfo\JWT;
use POGOProtos\Networking\Envelopes\ResponseEnvelope;

class RequestHandler {
  const APIURL = "https://pgorelease.nianticlabs.com/plfe/rpc";

  protected $logger;
  protected $session;
  protected $RPCId;
  protected $client;

  public function __construct(Session $session) {
    $this->logger = $session->getLogger();
    $this->session = $session;
    $this->RPCId = mt_rand();

    $this->client = new Client([
      "headers" => ["User-Agent" => "Niantic App"]
    ]);
  }

  protected function getRPCId() : \int {
    return ++$this->RPCId;
  }

  protected function getDefaultRequests() : array {
    $reqs = [];
    // TODO: default requests
/*    $reqs[] = new R\HatchedEggsRequest();

    $inventory = new ProtoRequest();
    $inventory->setRequestType(RequestType::GET_INVENTORY);
    $inventoryMessage = new GetInventoryMessage();
    $inventoryMessage->setLastTimestampMs(0);
    $inventory->setRequestMessage($inventoryMessage->toProtobuf());
    $reqs[] = $inventory;

    $badges = new ProtoRequest();
    $badges->setRequestType(RequestType::CHECK_AWARDED_BADGES);
    $reqs[] = $badges;

    $settings = new ProtoRequest();
    $settings->setRequestType(RequestType::DOWNLOAD_SETTINGS);
    $settingsMessage = new DownloadSettingsMessage();
    $settingsMessage->setHash("4a2e9bc330dae60e7b74fc85b98868ab4700802e");
    $settings->setRequestMessage($settingsMessage->toProtobuf());
    $reqs[] = $settings;*/

    return $reqs;
  }

  /**
   * @param Request $req
   * @param bool|true $defaults
   * @param bool|false $createEndpoint
   * @throws Exception
   */
  public function execute(Request $req, $defaults = true, $createEndpoint = false) {
    $this->logger->info("Execute ".get_class($req));

    $env = new RequestEnvelope();
    $env->setStatusCode(2);
    $env->setRequestId($this->getRPCId());

    $location = $this->session->getLocation();

    $env->setLatitude($location->getLatitude());
    $env->setLongitude($location->getLongitude());
    $env->setAltitude($location->getAltitude());

    if ($this->session->hasAuthTicket()) {
      $this->logger->debug("Existing auth ticket");
      $env->setAuthTicket($this->session->getAuthTicket()->toProto());
    }
    else {
      $this->logger->debug("Auth with token");
      $info = new AuthInfo();
      $info->setProvider($this->session->getProvider());

      $token = new JWT();
      $token->setContents($this->session->getToken());
      $token->setUnknown2(59);

      $info->setToken($token);
      $env->setAuthInfo($info);
    }
    $env->setUnknown12(989);

    $env->addRequests($req->toProto());
    if ($defaults) {
      $defaultReqs = $this->getDefaultRequests();
      foreach ($defaultReqs as $defaultReq) {
        $env->addRequests($defaultReq->toProto());
      }
    }

    $URL = $this->session->hasEndpoint() ? $this->session->getEndpoint() : self::APIURL;

    $resp = $this->client->post($URL, ["body" => $env->toStream()]);
    $respEnv = new ResponseEnvelope((string) $resp->getBody());
    if ($respEnv->hasAuthTicket()) {
      $this->logger->info("Received auth ticket");
      $this->session->setAuthTicket(new AuthTicket($respEnv->getAuthTicket()));
    }

    if ($respEnv->hasApiUrl()) {
      $endpoint = $respEnv->getApiUrl();
      $this->logger->info("Received API endpoint ".$endpoint);
      $this->session->setEndpoint("https://".$endpoint."/rpc");
    }

    if ($createEndpoint) {
      return;
    }

    $count = 0;
    $firstReturn = null;
    foreach ($respEnv->getReturnsList() as $return) {
      if ($count == 0) {
        $firstReturn = $return;
      }
      $count++;
    }

    if ($count < 1) {
      throw new Exception("No response found");
    }

    $this->logger->debug("Response envelope:");
    $lines = explode("\n", print_r($respEnv, true));
    foreach ($lines as $line) {
      $this->logger->debug($line);
    }

    $req->setRawResponse($firstReturn);
    if ($defaults) {
      // TODO: handle default requests
    }
  }
}
