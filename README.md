# EArray

[![Build Status](https://travis-ci.org/kohkimakimoto/EArray.png?branch=master)](https://travis-ci.org/kohkimakimoto/EArray)
[![Coverage Status](https://coveralls.io/repos/kohkimakimoto/EArray/badge.png?branch=master)](https://coveralls.io/r/kohkimakimoto/EArray?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kohkimakimoto/earray/v/stable.png)](https://packagist.org/packages/kohkimakimoto/earray)

EArray is a small PHP class to provide convenient ways to access a PHP array.

* Convenient accessing to a nested array.
* You can use a default value when you try to get a value of array.
* You can use this object as a normal array (Implementing `ArrayAccess`, `Iterator` and `Countable` interfase).
* It has some convenient methods for array: `each`, `filter`, `sort`.
* You can register custom methods to array.

It aims to remove code that checks array key existence. Especially for a nested array.
Do you hate the code like the below?

```php
$val = null;
$arr2 = isset($arr["key"]) ? $arr["key"] : null;
if (is_array($arr2)) {
    $val = isset($arr2["key2"]) ? $arr2["key2"] : null;
}

echo $val;
```

You can write same things using EArray object.

```php
echo $earray->get("key/key2", null);
```

## Requirement

PHP5.3 or later.

## Installation

You can use composer installation.
Make `composer.json` file like the following.

```json
{
      "require": {
          "kohkimakimoto/earray": "2.2.*"
      }
}
```

And run composer install command.

```
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

## Usage

* [Basic operations](#basic-operations)
* [Convenient methods](#convenient-methods)
  * [each](#each)
  * [filter](#filter)
  * [sort](#sort)
* [Registering a custom method](#registering-a-custom-method)

### Basic operations

You can use `get`, `set`, `exists` and `delete` methods.

```php
<?php
use Kohkimakimoto\EArray\EArray;

$earray = new EArray(array("foo" => "bar"));
$earray->get("foo");             # "bar"
$earray->get("foo2");            # null
$earray->get("foo2", "default"); # "default"

$earray->set("foo", "bar2");
$earray->get("foo");             # "bar2"

$earray->delete("foo");
$earray->get("foo");             # null

$earray->exists("foo2")          # true
$earray->exists("foo")           # false
```

And you can use a delimiter (default `/`) for accessing nested array values.

```php
$earray = new EArray(
    array(
        "foo" => array(
            "foo2" => array(
                "foo3",
                "foo4",
                ),
            "foo2-1" => "foo5",
            ),
        "bar",
        "hoge",
        )
);

// You can get a value from a nested array.
$earray->get("foo/foo2-1");             # "foo5".
$earray->get("foo");                    # EArray(array("foo2" => array("foo3","foo4",),"foo2-1" => "foo5"))
$earray->get("foo")->get("foo2-1");     # "foo5".
$earray->get("foo")->toArray();         # array("foo2" => array("foo3","foo4",),"foo2-1" => "foo5")

// You can change a delimiter by the third argument.
$earray->get("foo.foo2-1", null, ".");  # "foo5"

// You can set a value to a nested array.
$earray->set("foo/foo2-1", "foo5-modify");
$earray->get("foo/foo2-1");             # "foo5-modify".

// You can delete a value from a nested array.
$earray->delete("foo/foo2-1");
$earray->get("foo/foo2-1");             # null

// You can check a value existing from a nested array.
$earray->exists("foo/foo2-1")          # false
```

You can change the default delimiter.

```php
// by the constructor's second argument.
$earray = new EArray(array("foo" => array("bar" => "value")), ".");

$earray->get("foo.bar"));    // "value"

// by the setDelimiter method.
$earray->setDelimiter("-");
$earray->get("foo-bar"));    // "value"
```

### Convenient methods

#### each

```php
$earray = new EArray(
    array(
        "foo" => "aaa",
        "bar" => "bbb",
        "hoge" => "eee",
        )
);

$earray->each(function($key, $value) {
    echo $key.":".$value."\n";  // foo:aaa
                                // bar:bbb
                                // hoge:eee
});

$earray->each(function($value) {
    echo $value."\n";  // aaa
                       // bbb
                       // eee
});
```

#### filter

```php
$earray = new EArray(
    array(
        "kohki" => 34,
        "alice" => 12,
        "bob"   => 44,
        )
);

$arr = $earray->filter(function($key, $value){
    if ($value >= 20) {
        return true;
    } else {
        return false;
    }
})->toArray(); // array("kohki" => 34, "bob" => 44)
```

#### sort

```php
$array = array();
$array["f"]["details"]["weight"] = 1;
$array["f"]["details"]["position"] = 34;
$array["e"]["details"]["weight"] = 2;
$array["e"]["details"]["position"] = 33;
$array["d"]["details"]["weight"] = 3;
$array["d"]["details"]["position"] = 22;
$array["c"]["details"]["weight"] = 4;
$array["c"]["details"]["position"] = 11;
$array["b"]["details"]["weight"] = 5;
$array["b"]["details"]["position"] = 2;
$array["a"]["details"]["weight"] = 6;
$array["a"]["details"]["position"] = 1;

$earray = new EArray($array);
$earray->sortByValue(function($one, $another){

    $v1 = $one->get("details/position");
    $v2 = $another->get("details/position");

    return $v1 - $v2;

})->toArray();

// Sort by details/position
// array("a" => array(...), "b" => array(...), "c" => array(...), "d" => array(...), ...)
```

### Registering a custom method

You can register custom methods to array.

```php
$earray = new EArray(
    array(
        "kohki" => 30,
        "taro" => 40,
        "masaru" => 50,
        )
);

$earray->register("getAverage", function ($earray) {
    $total = 0;
    foreach ($earray as $v) {
        $total += $v;
    }
    return $total / count($earray);
});

$earray->getAverage();  // 40

// using arguments when the method is called.
$earray->register("getAverageAndAddNumber", function ($earray, $number) {
    $total = 0;
    foreach ($earray as $v) {
        $total += $v;
    }
    $ave = $total / count($earray);
    return $ave + $number;
});

$earray->getAverageAndAddNumber(100);  // 140
```

## License

Apache License 2.0
