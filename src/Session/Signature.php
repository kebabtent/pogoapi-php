<?php
namespace POGOAPI\Session;

use POGOProtos\Networking\Envelopes\Signature as ProtoSignature;
use POGOProtos\Networking\Envelopes\RequestEnvelope;
use POGOAPI\Map\Location;
use POGOProtos\Networking\Envelopes\Unknown6;
use POGOProtos\Networking\Envelopes\Unknown6\Unknown2;

class Signature {
  public static function sign(Session $session, RequestEnvelope $env) {
    if (!$session->hasAuthTicket()) {
      return;
    }

    $location = $session->getLocation();
    $rawTicket = $session->getAuthTicket()->toProto()->toStream()->getContents();

    $protoSignature = new ProtoSignature();
    $protoSignature->setLocationHash1(self::generateLocation1($rawTicket, $location));

    $uk6 = new Unknown6();
    $uk6->setRequestType(6);

    $uk2 = new Unknown2();
    $uk2->setEncryptedSignature();

    $uk6->setUnknown2($uk2);
    $env->setUnknown6($uk6);

  }

  protected static function generateLocation1(\string $rawTicket, Location $location) : \int {
    $seed = (int) hexdec(xxhash32($rawTicket, 0x1B845238));
    return (int) hexdec(xxhash32($location->toBytes(), $seed));
  }

  protected static function generateLocation2(Location $location) : \int {
    return (int) hexdec(xxhash32($location->toBytes(), 0x1B845238));
  }

}