<?php
namespace POGOAPI\Session;

use POGOAPI\Util\Serializable;
use POGOProtos\Networking\Envelopes\AuthTicket as ProtoAuthTicket;

class AuthTicket implements Serializable {
  protected $start;
  protected $expire;
  protected $end;

  /**
   * @param ProtoAuthTicket $proto
   */
  public function __construct(ProtoAuthTicket $proto) {
    $this->start = $proto->getStart()->getContents();
    $this->expire = $proto->getExpireTimestampMs();
    $this->end = $proto->getEnd()->getContents();
  }

  /**
   * @return bool
   */
  public function isValid() {
    return $this->getTimeToExpire() > 0;
  }

  /**
   * @return int
   */
  public function getTimeToExpire() {
    return round($this->expire/1000) - time() - 3;
  }

  /**
   * @return ProtoAuthTicket
   */
  public function toProto() {
    $ticket = new ProtoAuthTicket();
    $ticket->setStart($this->start);
    $ticket->setExpireTimestampMs($this->expire);
    $ticket->setEnd($this->end);
    return $ticket;
  }

  /**
   * @return string
   */
  public function toBinary() {
    return $this->toProto()->toStream()->getContents();
  }

  /**
   * @return string
   */
  public function serialize() {
    return bin2hex($this->toBinary());
  }

  /**
   * @param string $raw
   * @return AuthTicket
   */
  public static function fromBinary($raw) {
    return new self(new ProtoAuthTicket($raw));
  }

  /**
   * @param string $hex
   * @return AuthTicket
   */
  public static function unserialize($hex) {
    return self::fromBinary(hex2bin($hex));
  }
}
