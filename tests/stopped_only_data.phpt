--TEST--
pinba_flush can keep running timers when asked to flush only stopped ones
--SKIPIF--
<?php if (!extension_loaded("pinba")) print "skip"; ?>
--FILE--
<?php
pinba_reset();
ini_set("pinba.server", "");

$running = pinba_timer_start(["kind" => "running"]);
$stopped = pinba_timer_add(["kind" => "stopped"], 1.5);

var_dump(get_resource_type($running));
var_dump(get_resource_type($stopped));
var_dump(count(pinba_timers_get()));
var_dump(count(pinba_timers_get(PINBA_ONLY_STOPPED_TIMERS)));
var_dump(pinba_flush("", PINBA_FLUSH_ONLY_STOPPED_TIMERS));
var_dump(count(pinba_timers_get()));
var_dump(count(pinba_timers_get(PINBA_ONLY_STOPPED_TIMERS)));
var_dump(pinba_timer_stop($running));
var_dump(count(pinba_timers_get()));
?>
--EXPECT--
string(11) "pinba timer"
string(11) "pinba timer"
int(2)
int(1)
bool(true)
int(1)
int(0)
bool(true)
int(1)
