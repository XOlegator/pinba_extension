--TEST--
Per-timer rusage (timer_ru_utime=22 / timer_ru_stime=23) is serialized and aggregated
--SKIPIF--
<?php
if (!extension_loaded("pinba")) print "skip";
if (!class_exists("PinbaClient")) print "skip PinbaClient is unavailable";
?>
--FILE--
<?php
// Minimal top-level protobuf scanner returning [field_number => [values...]].
// Wire type 5 (32-bit) is decoded as a little-endian float, wire type 0 as a
// varint integer; length-delimited (2) and 64-bit (1) chunks are skipped. This
// lets the deterministic checks below assert the on-wire per-timer rusage
// fields (timer_ru_utime=22, timer_ru_stime=23) without any network I/O.
function pinba_decode(string $data): array
{
    $len = strlen($data);
    $pos = 0;
    $fields = [];
    $read_varint = function () use ($data, &$pos): int {
        $shift = 0;
        $value = 0;
        do {
            $byte = ord($data[$pos++]);
            $value |= ($byte & 0x7f) << $shift;
            $shift += 7;
        } while ($byte & 0x80);
        return $value;
    };
    while ($pos < $len) {
        $tag = $read_varint();
        $field = $tag >> 3;
        $wire = $tag & 7;
        if ($wire === 0) {
            $fields[$field][] = $read_varint();
        } elseif ($wire === 1) {
            $pos += 8;
        } elseif ($wire === 2) {
            $pos += $read_varint();
        } elseif ($wire === 5) {
            $fields[$field][] = unpack("g", substr($data, $pos, 4))[1];
            $pos += 4;
        } else {
            break;
        }
    }
    return $fields;
}

function make_client(): PinbaClient
{
    $client = new PinbaClient(["127.0.0.1:30002"]);
    $client->setHostname("worker-1");
    $client->setServername("frontend");
    $client->setScriptname("/rusage");
    return $client;
}

// Deterministic path: a single timer carries exactly the rusage it was given.
// 0.25, 0.125 and 0.5 are exact in both microsecond timeval and IEEE-754 float.
$client = make_client();
$client->addTimer(["op" => "db"], 0.5, [0.25, 0.125]);
$fields = pinba_decode($client->getData());

echo "single timer:\n";
var_dump($fields[10]); // timer_hit_count
var_dump($fields[11]); // timer_value
var_dump($fields[22]); // timer_ru_utime
var_dump($fields[23]); // timer_ru_stime

// Deterministic path: addTimer aggregates value, rusage and hit count for
// identical tags into a single on-wire timer entry.
$client = make_client();
$client->addTimer(["op" => "db"], 0.5, [0.25, 0.125]);
$client->addTimer(["op" => "db"], 0.5, [0.25, 0.125]);
$fields = pinba_decode($client->getData());

echo "aggregated timer:\n";
var_dump($fields[10]);
var_dump($fields[11]);
var_dump($fields[22]);
var_dump($fields[23]);

// Environment-sensitive path: pinba_timer_start/stop captures a real getrusage
// baseline and delta. The exact values depend on the host, so only assert that
// the keys are present and non-negative.
pinba_reset();
$timer = pinba_timer_start(["op" => "work"]);
$sink = 0;
for ($i = 0; $i < 100000; $i++) {
    $sink += $i;
}
pinba_timer_stop($timer);
$info = pinba_timer_get_info($timer);

echo "runtime rusage:\n";
var_dump(array_key_exists("ru_utime", $info));
var_dump(array_key_exists("ru_stime", $info));
var_dump($info["ru_utime"] >= 0);
var_dump($info["ru_stime"] >= 0);
?>
--EXPECT--
single timer:
array(1) {
  [0]=>
  int(1)
}
array(1) {
  [0]=>
  float(0.5)
}
array(1) {
  [0]=>
  float(0.25)
}
array(1) {
  [0]=>
  float(0.125)
}
aggregated timer:
array(1) {
  [0]=>
  int(2)
}
array(1) {
  [0]=>
  float(1)
}
array(1) {
  [0]=>
  float(0.5)
}
array(1) {
  [0]=>
  float(0.25)
}
runtime rusage:
bool(true)
bool(true)
bool(true)
bool(true)
