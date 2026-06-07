--TEST--
pinba_flush sends a UDP protobuf packet with the expected userland markers
--SKIPIF--
<?php
if (!extension_loaded("pinba")) {
    print "skip";
}
if (!function_exists("stream_socket_server")) {
    print "skip stream_socket_server is unavailable";
}
?>
--FILE--
<?php
pinba_reset();

$socket = stream_socket_server("udp://127.0.0.1:0", $errno, $errstr, STREAM_SERVER_BIND);
if ($socket === false) {
    die("failed to create udp listener: $errstr\n");
}

$address = stream_socket_get_name($socket, false);
if ($address === false) {
    die("failed to resolve udp listener address\n");
}

ini_set("pinba.server", $address);
ini_set("pinba.enabled", "1");

var_dump(pinba_tag_set("req-tag-name", "req-tag-value"));
$running = pinba_timer_start(["timer-kind" => "run-only-marker"]);
$stopped = pinba_timer_add(["timer-kind" => "stop-only-marker"], 1.75);

var_dump(get_resource_type($running));
var_dump(get_resource_type($stopped));
var_dump(pinba_flush("custom-script-name", PINBA_FLUSH_ONLY_STOPPED_TIMERS | PINBA_FLUSH_RESET_DATA));

stream_set_timeout($socket, 1);
$peer = null;
$packet = stream_socket_recvfrom($socket, 65535, 0, $peer);

var_dump(is_string($packet));
var_dump(strlen($packet) > 0);
var_dump(strpos($packet, "custom-script-name") !== false);
var_dump(strpos($packet, "req-tag-name") !== false);
var_dump(strpos($packet, "req-tag-value") !== false);
var_dump(strpos($packet, "stop-only-marker") !== false);
var_dump(strpos($packet, "run-only-marker") === false);
var_dump(strpos($peer, "127.0.0.1:") === 0);
var_dump(count(pinba_timers_get()));

fclose($socket);
?>
--EXPECT--
bool(true)
string(11) "pinba timer"
string(11) "pinba timer"
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
int(1)
