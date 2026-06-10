--TEST--
pinba_timers_stop() stops every running timer in one call
--SKIPIF--
<?php if (!extension_loaded("pinba")) print "skip"; ?>
--FILE--
<?php
pinba_reset();

$first = pinba_timer_start(["x" => "1"]);
$second = pinba_timer_start(["y" => "2"]);

var_dump(count(pinba_timers_get()));
var_dump(count(pinba_timers_get(PINBA_ONLY_STOPPED_TIMERS)));

var_dump(pinba_timers_stop());

var_dump(count(pinba_timers_get(PINBA_ONLY_STOPPED_TIMERS)));
var_dump(count(pinba_timers_get()));
?>
--EXPECT--
int(2)
int(0)
bool(true)
int(2)
int(2)
