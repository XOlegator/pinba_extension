--TEST--
Float timing inputs are sanitized or rejected when they overflow the Pinba wire format
--SKIPIF--
<?php
if (!extension_loaded("pinba")) print "skip";
if (!class_exists("PinbaClient")) print "skip PinbaClient is unavailable";
?>
--FILE--
<?php
set_error_handler(function ($errno, $errstr) {
    echo $errstr, "\n";
    return true;
});

pinba_reset();

$timer = pinba_timer_add(["op" => "x"], 1e100);
var_dump(get_resource_type($timer));
var_dump(pinba_timer_get_info($timer)["value"]);

var_dump(pinba_request_time_set(1e100));
$info = pinba_get_info();
var_dump($info["req_time"] < 10);

$client = new PinbaClient(["127.0.0.1:1"]);
var_dump($client->setRequestTime(1e100));
var_dump($client->setRusage([1e100, 0.0]));
var_dump($client->addTimer(["a" => "b"], 1e100));
var_dump($client->addTimer(["a" => "b"], 1.0, [1e100, 0.0]));
?>
--EXPECT--
pinba_timer_add(): non-finite or overflowing time value passed, changing it to 0
string(11) "pinba timer"
float(0)
pinba_request_time_set(): non-finite or overflowing request time value passed, changing it to 0
bool(true)
bool(true)
PinbaClient::setRequestTime(): request_time must be finite, non-negative and fit into a Pinba float
bool(false)
PinbaClient::setRusage(): rusage values must be finite, non-negative and fit into a Pinba float
bool(false)
PinbaClient::addTimer(): timer value must be finite, non-negative and fit into a Pinba float
bool(false)
PinbaClient::addTimer(): timer rusage values must be finite, non-negative and fit into a Pinba float
bool(false)
