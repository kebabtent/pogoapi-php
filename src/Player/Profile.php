<?php
namespace POGOAPI\Player;

use POGOAPI\Session\Session;
use POGOAPI\Session\Requests\PlayerRequest;

class Profile {
  protected $logger;
  protected $session;
  protected $data;

  /**
   * @param Session $session
   */
  public function __construct(Session $session) {
    $this->logger = $session->getLogger();
    $this->session = $session;
  }

  /**
   * @param $name
   * @param $args
   * @return mixed
   */
  public function __call($name, $args) {
    return call_user_func_array(array($this->data, $name), $args);
  }

  /**
   * @return mixed
   */
  public function getData() {
    return $this->data;
  }

  /**
   * @return null
   */
  public function update() {
    $this->logger->debug("Update profile");

    $req = new PlayerRequest($this->session);
    $req->execute();
    $this->data = $req->getResponse()->getPlayerData();
  }
}
