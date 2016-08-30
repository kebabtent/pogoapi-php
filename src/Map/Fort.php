<?php
namespace POGOAPI\Map;

use POGOAPI\Session\Requests\FortDetailsRequest;
use POGOProtos\Map\Fort\FortData;
use POGOAPI\Session\Session;

abstract class Fort {
  protected $session;

  protected $id;
  protected $location;

  protected $hasDetails;
  protected $name;
  protected $imageURL;
  protected $description;

  /**
   * @param Session $session
   * @param FortData $data
   */
  public function __construct(Session $session, FortData $data) {
    $this->session = $session;

    $this->id = $data->getId();
    $this->location = new Location($data->getLatitude(), $data->getLongitude());
  }

  public function getDetails() {
    if ($this->hasDetails) {
      return;
    }

    $req = new FortDetailsRequest($this->session, $this);
    $req->execute();
    $resp = $req->getResponse();

    $this->hasDetails = true;
    $this->name = utf8_decode($resp->getName());
    $this->imageURL = NULL;
    if ($resp->hasImageUrlsList()) {
      $urls = $resp->getImageUrlsList();
      foreach ($urls as $url) {
        $this->imageURL = (string) $url;
        break;
      }
    }
    $this->description = utf8_decode($resp->getDescription());
  }

  /**
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return Location
   */
  public function getLocation() {
    return $this->location;
  }

  /**
   * @return string
   */
  public function getName() {
    $this->getDetails();
    return $this->name;
  }

  /**
   * @return string[]
   */
  public function getImageURL() {
    $this->getDetails();
    return $this->imageURL;
  }

  /**
   * @return string
   */
  public function getDescription() {
    $this->getDetails();
    return $this->description;
  }
}