<?php
namespace Macaron;

class Foo {
  public function foo() {
  }
}

class MacaronTest extends \PHPUnit_Framework_TestCase {

  public function testCodecSane() {
    $secret = 'tOpCekpet';
    $data = ["1", 'foo' => 'bar'];
    $token = Macaron::encode($data, $secret);
    $this->assertInternalType('string', $token);
    $this->assertSame($data, Macaron::decode($token, $secret));
  }

  public function testCodecDoClasses() {
    $secret = 'tOpCekpet';
    $obj = new Foo();
    $obj->bar = 'baz';
    $data = [1, 'foo' => 'bar', $obj];
    $token = Macaron::encode($data, $secret);
    $data2 = Macaron::decode($token, $secret);
    $this->assertSame(1, $data2[0]);
    $this->assertSame('bar', $data2['foo']);
    $this->assertInstanceOf('\\Macaron\\Foo', $data2[1]);
    $this->assertTrue(method_exists($data[1], 'foo'));
    $this->assertSame('baz', $data2[1]->bar);
  }

  public function testForged() {
    $secret = 'tOpCekpet';
    $data = [1, 'foo' => 'bar'];
    $token = Macaron::encode($data, $secret);
    $this->assertFalse(Macaron::decode($token . '1', $secret));
    $this->assertFalse(Macaron::decode($token . '?', $secret));
    $this->assertFalse(Macaron::decode($token . '*', $secret));
    $this->assertFalse(Macaron::decode($token, $secret . '1'));
    $this->assertFalse(Macaron::decode($token . '1', $secret . '2'));
  }

  public function testExpiring() {
    $secret = 'tOpCekpet';
    $data = [1, 'foo' => 'bar'];
    $token = Macaron::encode($data, $secret, "+3 seconds");
    $this->assertSame($data, Macaron::decode($token, $secret));
    sleep(1);
    $this->assertSame($data, Macaron::decode($token, $secret));
    sleep(1);
    $this->assertSame($data, Macaron::decode($token, $secret));
    sleep(2);
    $this->assertNull(Macaron::decode($token, $secret));
  }

  public function testExpired() {
    $secret = 'tOpCekpet';
    $data = [1, 'foo' => 'bar'];
    $token = Macaron::encode($data, $secret, "-3 seconds");
    $this->assertNull(Macaron::decode($token, $secret));
  }

}
