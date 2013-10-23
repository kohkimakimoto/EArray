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
                    ), $earray->get("foo")->getArray());

        $this->assertEquals(array(
                        "foo3",
                        "foo4",
                        ), $earray->get("foo")->get("foo2")->getArray());

        $this->assertEquals(array(
                        "foo3",
                        "foo4",
                        ), $earray->get("foo/foo2")->getArray());

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
            "foo4" => "bar4",
        ));

        unset($earray["foo4"]);
        isset($earray["foo4"]);
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
    }
}