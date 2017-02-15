<?php
namespace Macaron;

class Pasta extends Macaron {

  static protected function serialize($data) {
    return json_encode($data);
  }

  static protected function unserialize($string) {
    return json_decode($string, true);
  }

}

class MacaronJSONTest extends \PHPUnit_Framework_TestCase {

  public function testCodecSane() {
    $secret = 'tOpCekpet';
    $data = ["1", 'foo' => 'bar'];
    $token = Pasta::encode($data, $secret);
    $this->assertInternalType('string', $token);
    $this->assertSame(['1', 'foo' => 'bar'], Pasta::decode($token, $secret));
  }

  public function testForged() {
    $secret = 'tOpCekpet';
    $data = [1, 'foo' => 'bar'];
    $token = Pasta::encode($data, $secret);
    $this->assertFalse(Pasta::decode($token . '1', $secret));
    $this->assertFalse(Pasta::decode($token, $secret . '1'));
    $this->assertFalse(Pasta::decode($token . '1', $secret . '2'));
  }

  public function testExpiring() {
    $secret = 'tOpCekpet';
    $data = [1, 'foo' => 'bar'];
    $token = Pasta::encode($data, $secret, "+3 seconds");
    $this->assertSame($data, Pasta::decode($token, $secret));
    sleep(1);
    $this->assertSame($data, Pasta::decode($token, $secret));
    sleep(1);
    $this->assertSame($data, Pasta::decode($token, $secret));
    sleep(2);
    $this->assertNull(Pasta::decode($token, $secret));
  }

  public function testExpired() {
    $secret = 'tOpCekpet';
    $data = [1, 'foo' => 'bar'];
    $token = Pasta::encode($data, $secret, "-3 seconds");
    $this->assertNull(Pasta::decode($token, $secret));
  }

}
