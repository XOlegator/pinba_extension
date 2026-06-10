--TEST--
pinba_get_data() serializes the procedural request and dictionary-encodes tags
--SKIPIF--
<?php if (!extension_loaded("pinba")) print "skip"; ?>
--FILE--
<?php
require __DIR__ . '/pinba_wire.inc';

pinba_reset();
ini_set("pinba.server", ""); // never touch the network

pinba_hostname_set("worker-1");
pinba_server_name_set("frontend");
pinba_script_name_set("/checkout");
pinba_schema_set("https");
pinba_request_time_set(0.5);
pinba_tag_set("env", "prod");
pinba_tag_set("region", "eu");

$fields = pinba_wire_decode(pinba_get_data());

// Required/optional scalar string fields land at their documented positions.
var_dump($fields[1][0]);  // hostname
var_dump($fields[2][0]);  // server_name
var_dump($fields[3][0]);  // script_name
var_dump($fields[19][0]); // schema
var_dump($fields[7][0]);  // request_time (exact in float)

// Request tags (fields 20/21) resolve through the string dictionary (field 15).
var_dump(pinba_wire_request_tags($fields));

// The dictionary must not contain duplicate strings.
$dict = $fields[15];
var_dump(count($dict) === count(array_unique($dict)));
var_dump(count($dict));
?>
--EXPECT--
string(8) "worker-1"
string(8) "frontend"
string(9) "/checkout"
string(5) "https"
float(0.5)
array(2) {
  ["env"]=>
  string(4) "prod"
  ["region"]=>
  string(2) "eu"
}
bool(true)
int(4)
