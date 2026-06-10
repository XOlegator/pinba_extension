--TEST--
Timer tags/data replace overwrite (vs merge); client setTimer overwrites, addTimer aggregates
--SKIPIF--
<?php
if (!extension_loaded("pinba")) print "skip";
if (!class_exists("PinbaClient")) print "skip PinbaClient is unavailable";
?>
--FILE--
<?php
require __DIR__ . '/pinba_wire.inc';

pinba_reset();

// Merge keeps existing entries and adds new ones.
$timer = pinba_timer_add(["a" => "1"], 1.0, ["d1" => "x"]);
pinba_timer_tags_merge($timer, ["b" => "2"]);
pinba_timer_data_merge($timer, ["d2" => "y"]);
$info = pinba_timer_get_info($timer);
ksort($info["tags"]);
ksort($info["data"]);
echo "merged tags:  ";
var_export($info["tags"]);
echo "\nmerged data:  ";
var_export($info["data"]);
echo "\n";

// Replace discards the previous tags/data entirely.
var_dump(pinba_timer_tags_replace($timer, ["c" => "3"]));
var_dump(pinba_timer_data_replace($timer, ["d3" => "z"]));
$info = pinba_timer_get_info($timer);
echo "replaced tags: ";
var_export($info["tags"]);
echo "\nreplaced data: ";
var_export($info["data"]);
echo "\n";

// Replacing with null data resets it to nothing.
var_dump(pinba_timer_data_replace($timer, null));
var_dump(pinba_timer_get_info($timer)["data"]);

// Client setTimer overwrites a same-tags timer; addTimer aggregates it.
$overwrite = new PinbaClient(["127.0.0.1:1"]);
$overwrite->setTimer(["op" => "x"], 0.25);
$overwrite->setTimer(["op" => "x"], 0.5);
$fields = pinba_wire_decode($overwrite->getData());
echo "setTimer value/hit: ";
var_dump($fields[11], $fields[10]);

$aggregate = new PinbaClient(["127.0.0.1:1"]);
$aggregate->addTimer(["op" => "x"], 0.25);
$aggregate->addTimer(["op" => "x"], 0.5);
$fields = pinba_wire_decode($aggregate->getData());
echo "addTimer value/hit: ";
var_dump($fields[11], $fields[10]);
?>
--EXPECT--
merged tags:  array (
  'a' => '1',
  'b' => '2',
)
merged data:  array (
  'd1' => 'x',
  'd2' => 'y',
)
bool(true)
bool(true)
replaced tags: array (
  'c' => '3',
)
replaced data: array (
  'd3' => 'z',
)
bool(true)
NULL
setTimer value/hit: array(1) {
  [0]=>
  float(0.5)
}
array(1) {
  [0]=>
  int(1)
}
addTimer value/hit: array(1) {
  [0]=>
  float(0.75)
}
array(1) {
  [0]=>
  int(2)
}
