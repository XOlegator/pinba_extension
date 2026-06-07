--TEST--
pinba_flush can reset request state after sending
--SKIPIF--
<?php if (!extension_loaded("pinba")) print "skip"; ?>
--FILE--
<?php
pinba_reset();
ini_set("pinba.server", "127.0.0.1:30002");
ini_set("pinba.enabled", "1");

var_dump(pinba_tag_set("env", "prod"));
$timer = pinba_timer_add(["kind" => "db"], 1.25, ["phase" => "before-flush"]);
var_dump(get_resource_type($timer));
var_dump(count(pinba_timers_get()));
var_dump(pinba_flush("custom-script", PINBA_FLUSH_RESET_DATA));
var_dump(count(pinba_timers_get()));
?>
--EXPECT--
bool(true)
string(11) "pinba timer"
int(1)
bool(true)
int(0)
