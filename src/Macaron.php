<?php
namespace Macaron;

class Expiring {

  private $data = null;

  public function __construct($data, $validUntil) {
    $this->data = $data;
    $this->validUntil = $validUntil;
  }

  public function data() {
    return ($this->validUntil > time()) ? $this->data : null;
  }

}

class Macaron {

  public static function urlsafe_encode($string) {
    $data = base64_encode($string);
    $data = str_replace(['+', '/', '='], ['-', '_', ''], $data);
    return $data;
  }

  public static function urlsafe_decode($string) {
    $data = str_replace(['-', '_', '.'], ['+', '/', '='], $string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
      $data .= substr('====', $mod4);
    }
    return base64_decode($data);
  }

  // http://codereview.stackexchange.com/questions/13512/constant-time-string-comparision-in-php-to-prevent-timing-attacks
  private static function constantTimeEquals($safe, $user) {
    // Prevent issues if string length is 0
    $safe .= chr(0);
    $user .= chr(0);

    $safeLen = strlen($safe);
    $userLen = strlen($user);

    // Set the result to the difference between the lengths
    $result = $safeLen - $userLen;

    // Note that we ALWAYS iterate over the user-supplied length
    // This is to prevent leaking length information
    for ($i = 0; $i < $userLen; $i++) {
      // Using % here is a trick to prevent notices
      // It's safe, since if the lengths are different
      // $result is already non-0
      $result |= (ord($safe[$i % $safeLen]) ^ ord($user[$i]));
    }

    // They are only identical strings if $result is exactly 0...
    return $result === 0;
  }

  private static function sign($data, $key) {
    return hash_hmac('sha1', $data, $key, true);
  }

  private static function encrypt($data, $key) {
    $iv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
    $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CFB, $iv);
    return $iv . $encrypted;
  }

  private static function uncrypt($data, $key) {
    $iv = substr($data, 0, 16);
    $uncrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, substr($data, 16), MCRYPT_MODE_CFB, $iv);
    return $uncrypted;
  }

  public static function encode($data, $secret, $validUntil = false) {
    if ($validUntil !== false) {
      $data = new Expiring($data, strtotime($validUntil));
    }
    $key = md5($secret);
    $enc = self::encrypt(serialize($data), $key);
    return self::urlsafe_encode(self::sign($enc, $key) . $enc);
  }

  public static function decode($data, $secret) {
    $data = self::urlsafe_decode($data);
    $key = md5($secret);
    $sig = substr($data, 0, 20);
    $enc = substr($data, 20);
    // NB: constant time comparison!
    if (self::constantTimeEquals($sig, self::sign($enc, $key))) {
      $data = unserialize(self::uncrypt($enc, $key));
      // check whether token expired
      if ($data instanceof Expiring) {
        $data = $data->data();
      }
    } else {
      $data = false;
    }
    return $data;
  }

}
