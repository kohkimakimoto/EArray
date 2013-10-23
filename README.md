# EArray

[![Build Status](https://travis-ci.org/kohkimakimoto/EArray.png?branch=master)](https://travis-ci.org/kohkimakimoto/EArray)
[![Coverage Status](https://coveralls.io/repos/kohkimakimoto/EArray/badge.png?branch=master)](https://coveralls.io/r/kohkimakimoto/EArray?branch=master)

EArray is a PHP Class to provide convenient ways to access a PHP Array.

* Convenient accessing nested array.
* Supporting a default value.

## Requrement

PHP5.3 or later.

## Installation

You can use composer installation. 
Make `composer.json` file like the following.

```json
{
      "require": {
          "kohkimakimoto/earray": "dev-master"
      }
}
```

And run composer install command.

```
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

## Usage

Basic usage.

```php
<?php
$earray = new Kohkimakimoto\EArray\EArray(array("foo" => "bar"));
$earray->get("foo"); # "bar"
$earray->get("foo2"); # null
$earray->get("foo2", "default"); # "default"

```

For nested array.

```php
<?php
$earray = new Kohkimakimoto\EArray\EArray(
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

// You can get value from a nested array using a delimiter (default "/")
$earray->get("foo/foo2-1");   # "foo5".
$earray->get("foo");   # EArray(array("foo2" => array("foo3","foo4",),"foo2-1" => "foo5"))
$earray->get("foo")->getArray();   # array("foo2" => array("foo3","foo4",),"foo2-1" => "foo5")

// You can change a delimiter by the third argument.
$earray->get("foo.foo2-1", null, "."); # "foo5"

```





