[![Build Status](https://travis-ci.org/dvv/macaron.png?branch=master)](http://travis-ci.org/dvv/macaron)

Expiring Signed Encrypted Tokens for PHP
=====

Library for serializing PHP-serializable data to signed encrypted strings and reliably deserializing them back.

Basically a port of my [Termit](https://github.com/dvv/termit) library.

Now stopped using `mcrypt` extension.

Usage
-----

```php
// everlasting token
$token = \Macaron\Macaron::encode('whatever PHP can serialize', 'ver1STrongsikret');
echo \Macaron\Macaron::decode($token, 'ver1STrongsikret');
// 'whatever PHP can serialize'

echo \Macaron\Macaron::decode($token . 'FORGED!', 'ver1STrongsikret');
// false
echo \Macaron\Macaron::decode($token, 'ver1STrongsikret' . 'LEAKED!');
// false
```

```php
// expiring token
$token = \Macaron\Macaron::encode('whatever PHP can serialize', $secret, '+3 seconds');
echo \Macaron\Macaron::decode($token, 'ver1STrongsikret');
// 'whatever PHP can serialize'

sleep(3);
echo \Macaron\Macaron::decode($token, 'ver1STrongsikret');
// null
```

```php
// overriding serializer
class Pasta extends \Macaron\Macaron {

  static protected function serialize($data) {
    return json_encode($data);
  }

  static protected function unserialize($string) {
    return json_decode($string, true);
  }

}

$token = Pasta::encode('whatever JSON can contain', $secret, '+3 seconds');
echo Pasta::decode($token, 'ver1STrongsikret');
// 'whatever JSON ca contain'

sleep(3);
echo Pasta::decode($token, 'ver1STrongsikret');
// null
```

See tests for more.

License
-----

The MIT License (MIT)

Copyright © 2015 Vladimir Dronnikov <dronnikov@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
