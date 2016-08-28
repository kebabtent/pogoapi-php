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

  /**
   * @param Session $session
   */
  public function __construct(Session $session) {
    $this->logger = $session->getLogger();
    $this->session = $session;
    $this->RPCId = mt_rand();

    $this->client = new Client([
      "headers" => ["User-Agent" => "Niantic App"]
    ]);
  }

  /**
   * @return int
   */
  protected function getRPCId() {
    return ++$this->RPCId;
  }

  /**
   * @return Request[]
   */
  protected function getDefaultRequests() {
    $reqs = [];
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
   * @param Request[] $reqs
   * @param bool|false $createEndpoint
   * @param bool|false $redo
   * @throws Exception
   */
  public function execute($reqs, $createEndpoint = false, $redo = true) {
    $reqTypes = [];
    foreach ($reqs as $req) {
      $reqTypes[] = get_class($req);
    }
    $this->logger->info("Execute ".implode(", ", $reqTypes));

    $env = new RequestEnvelope();
    $env->setStatusCode(2);
    $env->setRequestId($this->getRPCId());

    $location = $this->session->getLocation();

    $env->setLatitude($location->getLatitude());
    $env->setLongitude($location->getLongitude());
    $env->setAltitude($location->getAltitude());

    if ($this->session->hasAuthTicket() && $this->session->getAuthTicket()->isValid()) {
      $this->logger->debug("Existing auth ticket");
      $env->setAuthTicket($this->session->getAuthTicket()->toProto());
    }
    else {
      if (!$this->session->hasToken()) {
        $this->session->authenticate();
      }

      $this->session->setEndpoint(null);

      $this->logger->debug("Auth with token");
      $info = new AuthInfo();
      $info->setProvider($this->session->getType()->getProvider());

      $token = new JWT();
      $token->setContents($this->session->getToken());
      $token->setUnknown2(59);

      $info->setToken($token);
      $env->setAuthInfo($info);
    }
    $env->setUnknown12(989);

    foreach ($reqs as $req) {
      $env->addRequests($req->toProto());
    }

    $URL = $this->session->hasEndpoint() ? $this->session->getEndpoint() : self::APIURL;

    Signature::sign($this->session, $env, $reqs);

    $resp = $this->client->post($URL, ["body" => $env->toStream()->getContents()]);
    $respEnv = new ResponseEnvelope($resp->getBody()->getContents());
    if ($respEnv->hasAuthTicket()) {
      $ticket = new AuthTicket($respEnv->getAuthTicket());
      $this->logger->info("Received auth ticket, expires in ".$ticket->getTimeToExpire()."s");
      $this->session->setAuthTicket($ticket);
    }

    if ($respEnv->hasApiUrl()) {
      $endpoint = $respEnv->getApiUrl();
      $this->logger->info("Received API endpoint ".$endpoint);
      $this->session->setEndpoint("https://".$endpoint."/rpc");
    }

    $statusCode = $respEnv->getStatusCode();
    $this->logger->debug("Status code ".$statusCode);
    if ($statusCode == 102) {
      // Some auth problem
      throw new Exception("Received status code 102: invalid auth");
    }
    elseif ($statusCode == 53) {
      // Wrong endpoint
      if ($redo) {
        sleep(3);
        $this->execute($reqs, $createEndpoint, false);
        return;
      }
      else {
        throw new Exception("Received status code 53: wrong endpoint");
      }
    }
    elseif ($statusCode == 3) {
      // Possible ban
      throw new Exception("Received status code 3: account possibly banned");
    }

    if (!$respEnv->hasReturnsList()) {
      throw new Exception("No responses given");
    }

    $returns = $respEnv->getReturnsList();
    $countResponses = $returns->count();
    if ($countResponses != count($reqs)) {
      throw new Exception("Invalid responses found");
    }

    $responses = [];
    foreach ($returns as $return) {
      $responses[] = $return;
    }

    array_map(function (Request $req, $response) {
      $req->setRawResponse($response);
    }, $reqs, $responses);
  }
}
