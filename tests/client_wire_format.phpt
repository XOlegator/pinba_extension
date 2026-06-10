--TEST--
PinbaClient::getData() encodes request rusage, optional fields and timer tags
--SKIPIF--
<?php
if (!extension_loaded("pinba")) print "skip";
if (!class_exists("PinbaClient")) print "skip PinbaClient is unavailable";
?>
--FILE--
<?php
require __DIR__ . '/pinba_wire.inc';

$client = new PinbaClient(["127.0.0.1:30002"]);
$client->setHostname("worker-1");
$client->setServername("frontend");
$client->setScriptname("/checkout");
$client->setSchema("https");
$client->setStatus(200);
$client->setMemoryFootprint(4096);
$client->setRusage([0.5, 0.25]);
// Two timers share the "group" and "op" tag names: they must be stored once in
// the dictionary and referenced by index from both timers.
$client->addTimer(["group" => "db", "op" => "select"], 0.25, [0.125, 0.0625]);
$client->addTimer(["group" => "cache", "op" => "get"], 0.125);

$fields = pinba_wire_decode($client->getData());

var_dump($fields[8][0]);  // request ru_utime
var_dump($fields[9][0]);  // request ru_stime
var_dump($fields[16][0]); // status
var_dump($fields[17][0]); // memory_footprint
var_dump($fields[19][0]); // schema

var_dump($fields[11]); // timer_value
var_dump($fields[12]); // timer_tag_count
var_dump($fields[22]); // timer_ru_utime
var_dump($fields[23]); // timer_ru_stime
var_dump(pinba_wire_timer_tags($fields));

// Dictionary de-duplication: shared tag names appear exactly once.
$dict = $fields[15];
var_dump(count($dict) === count(array_unique($dict)));
var_dump(count(array_keys($dict, "group")));
var_dump(count(array_keys($dict, "op")));
?>
--EXPECT--
float(0.5)
float(0.25)
int(200)
int(4096)
string(5) "https"
array(2) {
  [0]=>
  float(0.25)
  [1]=>
  float(0.125)
}
array(2) {
  [0]=>
  int(2)
  [1]=>
  int(2)
}
array(2) {
  [0]=>
  float(0.125)
  [1]=>
  float(0)
}
array(2) {
  [0]=>
  float(0.0625)
  [1]=>
  float(0)
}
array(2) {
  [0]=>
  array(2) {
    ["group"]=>
    string(2) "db"
    ["op"]=>
    string(6) "select"
  }
  [1]=>
  array(2) {
    ["group"]=>
    string(5) "cache"
    ["op"]=>
    string(3) "get"
  }
}
bool(true)
int(1)
int(1)
