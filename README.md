# EArray

EArray is a PHP Class to provide convenient ways to access a PHP Array.

## Requrement

PHP5.3 or later.

## Installation

You can use composer installation. 
Make `composer.json` file like the following.

```json
{
      "require": {
          "kohkimakimoto/earray": "0.1.*"
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
$earray = new EArray(array("foo" => "bar"));
$earray->get("foo"); # "bar"
$earray->get("foo2"); # null
$earray->get("foo2", "default"); # "default"

```

For nested array.

```php
<?php
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

$earray->get("foo/foo2-1");   # "foo5".
$earray->get("foo");   # EArray(array("foo2" => array("foo3","foo4",),"foo2-1" => "foo5"))
$earray->get("foo")->getArray();   # array("foo2" => array("foo3","foo4",),"foo2-1" => "foo5")

```





