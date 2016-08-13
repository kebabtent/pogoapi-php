<?php
namespace POGOAPI\Session;

use POGOProtos\Networking\Envelopes\AuthTicket as ProtoAuthTicket;

class AuthTicket {
  protected $proto;

  public function __construct(ProtoAuthTicket $proto) {
    $this->proto = $proto;
  }

  public function __call($name, $args) {
    return call_user_func_array(array($this->proto, $name), $args);
  }

  public function isValid() : \bool {
    return time() > $this->proto->getExpireTimestampMs()/1000 - 3;
  }

  public function toProto() : ProtoAuthTicket {
    return $this->proto;
  }

  public static function fromRaw($raw) : self {
    return new self(new ProtoAuthTicket($raw));
  }
}