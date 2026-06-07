--TEST--
Pinba timers can be listed and inspected without sending packets
--SKIPIF--
<?php if (!extension_loaded("pinba")) print "skip"; ?>
--FILE--
<?php
pinba_reset();

$running = pinba_timer_start(["kind" => "running"]);
$stopped = pinba_timer_add(["kind" => "stopped"], 1.5, ["phase" => "init"]);

var_dump(get_resource_type($running));
var_dump(get_resource_type($stopped));
var_dump(count(pinba_timers_get()));
var_dump(count(pinba_timers_get(PINBA_ONLY_STOPPED_TIMERS)));
var_dump(pinba_timer_stop($running));
var_dump(count(pinba_timers_get(PINBA_ONLY_STOPPED_TIMERS)));
var_dump(pinba_timer_data_merge($stopped, ["step" => "merge"]));
var_dump(pinba_timer_tags_merge($stopped, ["scope" => "full"]));

$info = pinba_timer_get_info($stopped);
ksort($info["data"]);
ksort($info["tags"]);

var_export([
    "started" => $info["started"],
    "value" => $info["value"],
    "tags" => $info["tags"],
    "data" => $info["data"],
]);
echo "\n";

var_dump(pinba_timer_delete($running));
var_dump(pinba_timer_delete($stopped));
var_dump(count(pinba_timers_get()));
?>
--EXPECT--
string(11) "pinba timer"
string(11) "pinba timer"
int(2)
int(1)
bool(true)
int(2)
bool(true)
bool(true)
array (
  'started' => false,
  'value' => 1.5,
  'tags' => 
  array (
    'kind' => 'stopped',
    'scope' => 'full',
  ),
  'data' => 
  array (
    'phase' => 'init',
    'step' => 'merge',
  ),
)
bool(true)
bool(true)
int(0)
