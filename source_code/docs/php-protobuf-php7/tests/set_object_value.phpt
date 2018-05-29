--TEST--
Protocol Buffers setting object value
--SKIPIF--
<?php require 'skipif.inc' ?>
--FILE--
<?php
require 'Foo.php';
require 'Bar.php';

$barDestructed = false;

class SpiedBar extends Bar {
    public function __destruct() {
        global $barDestructed;
        $barDestructed = true;
    }
}

$embedded = new SpiedBar();
$embedded->setDoubleField(2.0);

$foo = new Foo();
$foo->setEmbeddedField($embedded);

var_dump($embedded === $foo->getEmbeddedField());

$foo->setEmbeddedField(null);
var_dump($barDestructed);
var_dump($foo->getEmbeddedField());

$embedded = null;
var_dump($barDestructed);
?>
--EXPECT--
bool(true)
bool(false)
NULL
bool(true)
