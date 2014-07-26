<?php
namespace Test\Kohkimakimoto\EArray;

use Kohkimakimoto\EArray\EArray;

class EArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testObject()
    {
        $earray = new EArray(array("foo" => "bar"));
        $this->assertEquals("bar", $earray->get("foo"));
        $this->assertEquals(null, $earray->get("foo2"));
        $this->assertEquals("default", $earray->get("foo2", "default"));
        $this->assertEquals("bar", $earray->get("foo", "default"));

        $earray = new EArray(array("foo", "bar"));
        $this->assertEquals("foo", $earray->get(0));
        $this->assertEquals("bar", $earray->get(1));

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

        $this->assertEquals(array(
                    "foo2" => array(
                        "foo3",
                        "foo4",
                        ),
                    "foo2-1" => "foo5",
                    ), $earray->get("foo")->toArray());

        $this->assertEquals(array(
                        "foo3",
                        "foo4",
                        ), $earray->get("foo")->get("foo2")->toArray());

        $this->assertEquals(array(
                        "foo3",
                        "foo4",
                        ), $earray->get("foo/foo2")->toArray());

        $this->assertEquals("foo5", $earray->get("foo.foo2-1", null, "."));
    }

    public function testArrayAccess()
    {
        $earray = new EArray(array("foo" => "bar"));
        $this->assertEquals("bar", $earray["foo"]);

        $earray = new EArray(array(
            "foo" => "bar",
            "foo1" => "bar1",
            "foo2" => "bar2",
            "foo3" => "bar3",
            "foo4" => array("foo5" => "foo6"),
        ));

        $this->assertEquals("bar", $earray["foo"]);
        $this->assertEquals(array("foo5" => "foo6"), $earray["foo4"]->toArray());

        unset($earray["foo4"]);
        $this->assertEquals(false, isset($earray["foo4"]));

        $earray["foo4"] = "aaaa";

        $this->assertEquals("aaaa", $earray["foo4"]);
        $this->assertEquals("aaaa", $earray->get("foo4"));
    }

    public function testIterator()
    {

        $earray = new EArray(array(
            "foo" => "bar",
            "foo1" => "bar1",
            "foo2" => "bar2",
            "foo3" => "bar3",
            "foo4" => array("foo5" => "foo6"),
        ));

        $i = 0;
        $status = 0;
        foreach ($earray as $k => $v) {
            if ($i == 0) {
                $this->assertEquals("foo", $k);
                $this->assertEquals("bar", $v);
                $status++;
            }
            if ($i == 1) {
                $this->assertEquals("foo1", $k);
                $this->assertEquals("bar1", $v);
                $status++;
            }
            if ($i == 2) {
                $this->assertEquals("foo2", $k);
                $this->assertEquals("bar2", $v);
                $status++;
            }
            if ($i == 3) {
                $this->assertEquals("foo3", $k);
                $this->assertEquals("bar3", $v);
                $status++;
            }
            if ($i == 4) {
                $this->assertEquals("foo4", $k); 
                $this->assertEquals(array("foo5" => "foo6"), $v->toArray());
                $status++;
            }
            $i++;
        }

        $this->assertEquals(5, $status);
        $this->assertEquals(5, count($earray));
    }


    public function testSet()
    {
        $earray = new EArray(array("foo" => "bar"));
        $earray->set("foo", "bar2");
        $this->assertEquals("bar2", $earray->get("foo"));
    }

    public function testDelete()
    {
        $earray = new EArray(array("foo" => "bar"));
        $earray->delete("foo");
        $this->assertEquals(null, $earray->get("foo"));
        $this->assertEquals(false, $earray->exists("foo"));

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

        $this->assertEquals(
            array(
                "foo" => array(
                    "foo2-1" => "foo5",
                    ),
                "bar",
                "hoge",
                ),
            $earray->delete("foo/foo2")->toArray());

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

        try {
            $earray->delete("foo/foo2/aaaaa");
            $this->assertEquals(false, true);
        } catch (\RuntimeException $e) {
            $this->assertEquals(true, true);
        }

    }

    public function testConstructException()
    {
        try {
            $earray = new EArray("aaaaaaaa");
        } catch (\RuntimeException $e) {
            $this->assertEquals(true, true);
            return;
        }

        $this->assertEquals(true, false);
    }

    /*
    public function testSort()
    {

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
        $sortedArray = $earray->sort("details/position")->toArray();

        $this->assertEquals(array("details" => array("weight" => 6, "position" => 1)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 5, "position" => 2)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 4, "position" => 11)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 3, "position" => 22)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 2, "position" => 33)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 1, "position" => 34)), array_shift($sortedArray));
    }

    public function testSort2()
    {
        $array = array();
        $array["g"] = 34;
        $array["f"] = 33;
        $array["e"] = 22;
        $array["d"] = 11;
        $array["c"] = 2;
        $array["b"] = 2;
        $array["a"] = 1;

        $earray = new EArray($array);
        $sortedArray = $earray->sort()->toArray();

        $this->assertEquals(1, array_shift($sortedArray));
        $this->assertEquals(2, array_shift($sortedArray));
        $this->assertEquals(2, array_shift($sortedArray));
        $this->assertEquals(11, array_shift($sortedArray));
        $this->assertEquals(22, array_shift($sortedArray));
        $this->assertEquals(33, array_shift($sortedArray));
        $this->assertEquals(34, array_shift($sortedArray));
    }

    public function testSortByString()
    {
        $array = array();
        $array["f"]["details"]["weight"] = 1;
        $array["f"]["details"]["position"] = "C";
        $array["e"]["details"]["weight"] = 2;
        $array["e"]["details"]["position"] = "B";
        $array["d"]["details"]["weight"] = 3;
        $array["d"]["details"]["position"] = "ABC";
        $array["c"]["details"]["weight"] = 4;
        $array["c"]["details"]["position"] = "AAC";
        $array["b"]["details"]["weight"] = 5;
        $array["b"]["details"]["position"] = "AAB";
        $array["a"]["details"]["weight"] = 6;
        $array["a"]["details"]["position"] = "AAA";

        $earray = new EArray($array);
        $sortedArray = $earray->sort("details/position")->toArray();

        $this->assertEquals(array("details" => array("weight" => 6, "position" => "AAA")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 5, "position" => "AAB")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 4, "position" => "AAC")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 3, "position" => "ABC")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 2, "position" => "B")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 1, "position" => "C")), array_shift($sortedArray));
    }

    public function testRsort()
    {
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
        $sortedArray = $earray->rsort("details/position")->toArray();

        $this->assertEquals(array("details" => array("weight" => 1, "position" => 34)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 2, "position" => 33)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 3, "position" => 22)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 4, "position" => 11)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 5, "position" => 2)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 6, "position" => 1)), array_shift($sortedArray));
    }

    public function testRsortByString()
    {
        $array = array();
        $array["f"]["details"]["weight"] = 1;
        $array["f"]["details"]["position"] = "C";
        $array["e"]["details"]["weight"] = 2;
        $array["e"]["details"]["position"] = "B";
        $array["d"]["details"]["weight"] = 3;
        $array["d"]["details"]["position"] = "ABC";
        $array["c"]["details"]["weight"] = 4;
        $array["c"]["details"]["position"] = "AAC";
        $array["b"]["details"]["weight"] = 5;
        $array["b"]["details"]["position"] = "AAB";
        $array["a"]["details"]["weight"] = 6;
        $array["a"]["details"]["position"] = "AAA";

        $earray = new EArray($array);
        $sortedArray = $earray->rsort("details/position")->toArray();

        $this->assertEquals(array("details" => array("weight" => 1, "position" => "C")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 2, "position" => "B")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 3, "position" => "ABC")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 4, "position" => "AAC")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 5, "position" => "AAB")), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 6, "position" => "AAA")), array_shift($sortedArray));
    }
    */
   
    public function testSetUsingDelimiter()
    {
        // 1
        $earray = new EArray();
        $earray->set("aaa/bbb/ccc", "ValueOfCcc");

        $this->assertEquals(
            array("aaa" => array("bbb" => array("ccc" => "ValueOfCcc"))), 
            $earray->toArray());

        // 2
        $earray = new EArray(array("ddd" => array("ddd" => 1)));
        $earray->set("aaa/bbb/ccc", "ValueOfCcc");

        $this->assertEquals(
            array(
                "ddd" => array("ddd" => 1),
                "aaa" => array("bbb" => array("ccc" => "ValueOfCcc"))), 
            $earray->toArray());


        // 3
        $earray = new EArray(array("aaa" => array("ddd" => 1)));
        $earray->set("aaa/bbb/ccc", "ValueOfCcc");

        $this->assertEquals(
            array(
                "aaa" => array(
                    "ddd" => 1,
                    "bbb" => array("ccc" => "ValueOfCcc"))), 
            $earray->toArray());

        // 4
        $earray = new EArray(array("aaa" => array("bbb" => 1)));
        try {
            $earray->set("aaa/bbb/ccc", "ValueOfCcc");
        } catch (\RuntimeException $e) {
            $this->assertEquals(true, true);
        }

        $earray = new EArray(array("aaa" => array("ddd" => "fff")));
        $earray->set("aaa.bbb.ccc", "ValueOfCcc");
        $this->assertEquals(
            array(
                "aaa" => array("ddd" => "fff"),
                "aaa.bbb.ccc" => "ValueOfCcc",
                ), 
            $earray->toArray());


        // 5
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

        $earray->get("foo/foo2-1");             # "foo5".
        $earray->get("foo");                    # EArray(array("foo2" => array("foo3","foo4",),"foo2-1" => "foo5"))
        $earray->get("foo")->get("foo2-1");     # "foo5".
        $earray->get("foo")->toArray();         # array("foo2" => array("foo3","foo4",),"foo2-1" => "foo5")

        $earray->get("foo.foo2-1", null, ".");  # "foo5"

        $earray->set("foo/foo2-1", "foo5-modify");
        $this->assertEquals("foo5-modify", $earray->get("foo/foo2-1"));             # "foo5".
    }

    public function testUserSpecifiedDelimiter()
    {
        $earray = new EArray(array(
            "foo" => array("bar" => "value")
            ), ".");

        $this->assertEquals("value", $earray->get("foo.bar"));

        $earray->setDelimiter("+");
        $this->assertEquals("value", $earray->get("foo+bar"));
    }

    public function testKeys()
    {
        $earray = new EArray(
            array(
                "foo" => array(
                    "foo2" => array(
                        "foo3",
                        "foo4",
                        ),
                    "foo2-1" => "foo5",
                    ),
                "bar" => "bbbb",
                "hoge" => "eee",
                )
        );

        $this->assertEquals(array("foo", "bar", "hoge"), $earray->getKeys());
        $this->assertEquals(array("foo", "bar", "hoge"), $earray->keys());
    }

    public function testExists()
    {
        $earray = new EArray(array("foo", "bar"));
        $this->assertEquals(true, $earray->exists(0));
        $this->assertEquals(true, $earray->exists(1));
        $this->assertEquals(false, $earray->exists(3));

        $earray = new EArray(
            array(
                "foo" => array(
                    "foo2" => array(
                        "foo3",
                        "foo4",
                        ),
                    "foo2-1" => "foo5",
                    ),
                "bar" => "bbbb",
                "hoge" => "eee",
                )
        );

        $this->assertEquals(true, $earray->exists("foo/foo2"));
        $this->assertEquals(false, $earray->exists("foo/foo3"));
        $this->assertEquals(true, $earray->exists("foo.foo2", "."));
    }

    public function testEach()
    {
        $earray = new EArray(
            array(
                "foo" => array(
                    "foo2" => array(
                        "foo3",
                        "foo4",
                        ),
                    "foo2-1" => "foo5",
                    ),
                "bar" => "bbbb",
                "hoge" => "eee",
                )
        );

        try {
            $earray->each("aaa");
            $this->assertEquals(true, false);
        } catch (\RuntimeException $e) {
            $this->assertEquals(true, true);
        }

        $self = $this;
        $earray->each(function($value) use ($self){
            $self->assertNotEquals("hoge", $value);
        });


        $earray->each(function($key, $value) use ($self) {
            if ($key == "hoge") {
                $self->assertEquals("eee", $value);
            }
        });
    }

    public function testFilter()
    {
        $earray = new EArray(
            array(
                "foo" => array(
                    "foo2" => array(
                        "foo3",
                        "foo4",
                        ),
                    "foo2-1" => "foo5",
                    ),
                "bar" => "bbbb",
                "hoge" => "eee",
                )
        );

        try {
            $earray->filter("aaa");
            $this->assertEquals(true, false);
        } catch (\RuntimeException $e) {
            $this->assertEquals(true, true);
        }
        
        $filterdArray = $earray->filter(function($key, $value){
            if ($key == "foo" || $key == "hoge") {
                return true;
            } else {
                return false;
            }
        })->toArray();

        $this->assertEquals(
            array(
                "foo" => array(
                    "foo2" => array(
                        "foo3",
                        "foo4",
                        ),
                    "foo2-1" => "foo5",
                    ),
                "hoge" => "eee",
                ),
            $filterdArray);

        $earray = new EArray(
            array(
                "kohki" => 34,
                "alice" => 12,
                "bob"   => 44,
                )
        );
    }

    public function testToString()
    {
        $earray = new EArray(
            array(
                "foo" => "aaaa",
                "bar" => "bbbb",
                "hoge" => "eeee",
                )
        );

        $expectation = ""
            ."Array\n"
            ."(\n"
            ."    [foo] => aaaa\n"
            ."    [bar] => bbbb\n"
            ."    [hoge] => eeee\n"
            .")\n";
        $this->assertEquals($expectation, (string)$earray);
    }

    public function testSize()
    {
        $earray = new EArray(
            array(
                "foo" => "aaaa",
                "bar" => "bbbb",
                "hoge" => "eeee",
                )
        );

        $this->assertEquals(3, $earray->size());
    }

    public function testSort()
    {

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
        $sortedArray = $earray->sort(function($one, $another){

            $v1 = $one->get("details/position");
            $v2 = $another->get("details/position");

            if ($v1 == $v2) {
                return 0;
            } else if ($v1 > $v2) {
                return 1;
            } else {
                return -1;
            }

        })->toArray();

        $this->assertEquals(array("details" => array("weight" => 6, "position" => 1)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 5, "position" => 2)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 4, "position" => 11)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 3, "position" => 22)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 2, "position" => 33)), array_shift($sortedArray));
        $this->assertEquals(array("details" => array("weight" => 1, "position" => 34)), array_shift($sortedArray));
    }

}