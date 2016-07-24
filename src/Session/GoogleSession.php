<?php
namespace POGOAPI\Session;

use GuzzleHttp\Client;
use Exception;

class GoogleSession extends Session {
  protected $authClient;

  protected $username;
  protected $password;
  protected $androidId;
  protected $service;
  protected $app;
  protected $clientSig;
  protected $token;

  public function __construct($username, $password, $androidId = "3764d56d68ae549c", $service = "audience:server:client_id:848232511240-7so421jotr2609rmqakceuu1luuq0ptb.apps.googleusercontent.com", $app = "com.nianticlabs.pokemongo", $clientSig = "321187995bc7cdc2b5fc91b11a96e2baa8602c62") {
    parent::__construct();

    $this->username = $username;
    $this->password = $password;
    $this->androidId = $androidId;
    $this->service = $service;
    $this->app = $app;
    $this->clientSig = $clientSig;
    $this->token = false;

    $this->authClient = new Client([
      "base_uri" => "https://android.clients.google.com",
      "headers" => ["User-Agent" => "POGOAPI/1.0"]
    ]);
  }

  public function authenticate() {
    // Master login
    $loginRes = $this->authClient->post("auth", ["form_params" => [
      "accountType"     => "HOSTED_OR_GOOGLE",
      "Email"            => $this->username,
      "has_permission"  => 1,
      "add_account"     => 1,
      "Passwd"          => $this->password,
      "service"         => "ac2dm",
      "source"          => "android",
      "androidId"       => $this->androidId,
      "device_country"  => "us",
      "operatorCountry" => "us",
      "lang"            => "en",
      "sdk_version"     => 17
    ]]);

    $masterLogin = parse_ini_string($loginRes->getBody());
    if (!isset($masterLogin['Token'])) {
      throw new Exception("Failed master login");
    }

    // OAuth
    $oauthRes = $this->authClient->post("auth", ["form_params" => [
      "accountType"     => "HOSTED_OR_GOOGLE",
      "Email"           => $this->username,
      "has_permission"  => 1,
      "EncryptedPasswd" => $masterLogin['Token'],
      "service"         => $this->service,
      "source"          => "android",
      "androidId"       => $this->androidId,
      "app"             => $this->app,
      "client_sig"      => $this->clientSig,
      "device_country"  => "us",
      "operatorCountry" => "us",
      "lang"            => "en",
      "sdk_version"     => 17
    ]]);

    $auth = parse_ini_string($oauthRes->getBody());
    if (!isset($auth['Auth'])) {
      throw new Exception("Failed auth");
    }
    $this->token = $auth['Auth'];
  }

  public function getToken() {
    return $this->token;
  }

  public function setToken($token) {
    $this->token = $token;
  }

  public function getProvider() {
    return "google";
  }
}
