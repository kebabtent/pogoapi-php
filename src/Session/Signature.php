<?php
namespace POGOAPI\Session;

use Exception;
use POGOAPI\Map\Location;
use POGOAPI\Util\MicroTime;
use POGOAPI\Session\Requests\Request;
use POGOEncrypt\Encrypt;
use POGOProtos\Networking\Envelopes\Signature as ProtoSignature;
use POGOProtos\Networking\Envelopes\RequestEnvelope;
use POGOProtos\Networking\Envelopes\Unknown6;
use POGOProtos\Networking\Envelopes\Unknown6\Unknown2;

if (!function_exists("xxhash32") || !function_exists("xxhash64")) {
  throw new Exception("Extension php_xxhash is required");
}

class Signature {
  /**
   * @param Session $session
   * @param RequestEnvelope $env
   * @param Request[] $reqs
   * @throws Exception
   */
  public static function sign(Session $session, RequestEnvelope $env, $reqs) {
    if (!$session->hasAuthTicket()) {
      return;
    }

    $location = $session->getLocation();
    $rawTicket = $session->getAuthTicket()->toBinary();

    $microTime = MicroTime::get();

    $protoSignature = new ProtoSignature();
    $protoSignature->setTimestampSinceStart($microTime-$session->getStartMicroTime());
    // TODO: LocationFix
    // TODO: AndroidGpsInfo
    // TODO: SensorInfo
    // TODO: DeviceInfo
    // TODO: ActivityStatus
    $protoSignature->setLocationHash1(self::generateLocation1($rawTicket, $location));
    $protoSignature->setLocationHash2(self::generateLocation2($location));
    $protoSignature->setSessionHash($session->getSessionHash());
    $protoSignature->setTimestamp($microTime);

    foreach ($reqs as $req) {
      $protoSignature->addRequestHash(self::generateRequestHash($rawTicket, $req->toProto()->toStream()->getContents()));
    }

    $protoSignature->setUnknown25(0x898654dd2753a481);

    $uk6 = new Unknown6();
    $uk6->setRequestType(6);

    $uk2 = new Unknown2();
    $enc = Encrypt::encrypt($protoSignature->toStream()->getContents(), random_bytes(32));
    $uk2->setEncryptedSignature($enc);

    $uk6->setUnknown2($uk2);
    $env->setUnknown6($uk6);

    $session->getLogger()->debug("Signed request: ".strlen($enc)." bytes");
  }

  /**
   * @param string $rawTicket
   * @param Location $location
   * @return int
   */
  protected static function generateLocation1($rawTicket, Location $location) {
    $seed = (int) hexdec(xxhash32($rawTicket, 0x1B845238));
    return (int) hexdec(xxhash32($location->toBytes(), $seed));
  }

  /**
   * @param Location $location
   * @return int
   */
  protected static function generateLocation2(Location $location) {
    return (int) hexdec(xxhash32($location->toBytes(), 0x1B845238));
  }

  /**
   * @param string $rawTicket
   * @param string $rawRequest
   * @return int
   */
  public static function generateRequestHash($rawTicket, $rawRequest) {
    $seed = unpack("J", pack("H*", xxhash64($rawTicket, 0x1B845238)));
    $unpack = unpack("J", pack("H*", xxhash64($rawRequest, $seed[1])));
    return $unpack[1];
  }

}
