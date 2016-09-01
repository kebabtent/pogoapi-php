<?php
namespace POGOAPI\Session;

use Exception;
use Monolog\Logger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use POGOAPI\Map\Location;

/**
 * Class PTCSession
 * Inspired by pokapi https://github.com/Droeftoeter/pokapi
 * @package POGOAPI\Session
 */
class PTCSession extends Session {
  protected $authClient;

  const LOGIN_URL = "https://sso.pokemon.com/sso/login?service=https%3A%2F%2Fsso.pokemon.com%2Fsso%2Foauth2.0%2FcallbackAuthorize";
  const LOGIN_OAUTH = "https://sso.pokemon.com/sso/oauth2.0/accessToken";
  const LOGIN_SECRET = "w8ScCUXJQc6kXKw8FiOhd8Fixzht18Dq3PEVkUCP5ZPxtgyWsbTvWHFLm2wNY0JR";

  protected $username;
  protected $password;
  protected $token;

  /**
   * @param Logger $logger
   * @param Location $location
   * @param $username
   * @param $password
   */
  public function __construct(Logger $logger, Location $location, $username, $password) {
    parent::__construct($logger, $location);

    $this->username = $username;
    $this->password = $password;

    $this->authClient = new Client([
      "cookies" => true,
      "verifyPeer" => false,
      "connect_timeout" => 30
    ]);
  }

  /**
   * @throws Exception
   */
  public function authenticate() {
    $ticket = null;
    $executionToken = $this->fetchExecutionToken();
    try {
      $this->authClient->post(self::LOGIN_URL, [
        "headers" => ["User-Agent" => "niantic"],
        "allow_redirects" => [
          "on_redirect" => function(Request $request, Response $response) use (&$ticket) {
            // Extract ticket from HTTP-Location header.
            preg_match("/ticket=(.+)/", $response->getHeaderLine("Location"), $matches);
            if (isset($matches[1])) {
              $ticket = $matches[1];
            }
          }
        ],
        "form_params" => [
          "lt" => $executionToken->lt,
          "execution" => $executionToken->execution,
          "_eventId" => "submit",
          "username" => $this->username,
          "password" => $this->password,
        ]
      ]);
    }
    catch(ServerException $exception) {
      // This is expected...
    }

    if ($ticket === null) {
      throw new Exception("No token found");
    }

    // Make oAuth request
    try {
      $response = $this->authClient->post(self::LOGIN_OAUTH, [
        "headers" => ["User-Agent" => "niantic"],
        "form_params" => [
          "client_id" => "mobile-app_pokemon-go",
          "redirect_uri" => "https://www.nianticlabs.com/pokemongo/error",
          "client_secret" => self::LOGIN_SECRET,
          "grant_type" => "refresh_token",
          "code" => $ticket
        ]
      ]);
    } catch(ServerException $e) {
      throw new Exception($e);
    }

    parse_str($response->getBody()->getContents(), $data);
    if (!isset($data['access_token'])) {
      throw new Exception("No access token");
    }

    $this->token = $data['access_token'];
  }

  /**
   * @param int $retry
   * @return mixed
   * @throws Exception
   */
  protected function fetchExecutionToken($retry = 0) {
    if ($retry >= 3) {
      throw new Exception("Unable to fetch execution token");
    }

    try {
      $response = $this->authClient->get(self::LOGIN_URL, ["headers" => ["User-Agent" => "niantic"]]);
    }
    catch(ServerException $e) {
      sleep(1);
      return $this->fetchExecutionToken($retry+1);
    }

    if ($response->getStatusCode() !== 200) {
      throw new Exception("Wrong response ".$response->getStatusCode());
    }
    $jsonData = json_decode($response->getBody()->getContents());
    if (!is_null($jsonData)) {
      return $jsonData;
    }
    throw new Exception("Unable to fetch execution token (2)");
  }

  /**
   * @return bool
   */
  public function hasToken() {
    return !is_null($this->token);
  }

  /**
   * @return string
   * @throws Exception
   */
  public function getToken() {
    if (!$this->hasToken()) {
      throw new Exception("No token set");
    }
    return $this->token;
  }

  /**
   * @param string $token
   */
  public function setToken($token) {
    $this->token = $token;
  }

  /**
   * @return AccountType
   */
  public function getType() {
    return AccountType::PTC();
  }
}
