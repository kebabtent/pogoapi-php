<?php
namespace POGOAPI\Player;

use POGOAPI\Session\Session;
use POGOAPI\Session\Requests\PlayerRequest;

class Profile {
  protected $logger;
  protected $session;
  protected $data;

  public function __construct(Session $session) {
    $this->logger = $session->getLogger();
    $this->session = $session;
  }

  public function __call($name, $args) {
    return call_user_func_array(array($this->data, $name), $args);
  }

  public function getData() {
    return $this->data;
  }

  public function update() {
    $this->logger->debug("Update profile");

    $req = new PlayerRequest($this->session);
    $req->execute();
    $this->data = $req->getResponse()->getPlayerData();
    // $this->logger->debug(print_r($req->getResponse(), true));
  }
}
