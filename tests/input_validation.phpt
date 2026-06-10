--TEST--
Timer and tag input validation (empty tags, bad hit_count, negative value, rusage arity)
--SKIPIF--
<?php
if (!extension_loaded("pinba")) print "skip";
if (!class_exists("PinbaClient")) print "skip PinbaClient is unavailable";
?>
--FILE--
<?php
// Capture warnings deterministically regardless of the ini error settings.
set_error_handler(function ($errno, $errstr) {
    echo $errstr, "\n";
    return true;
});

pinba_reset();

// Procedural API.
var_dump(pinba_timer_start([]));
var_dump(pinba_timer_start(["a" => "b"], null, 0));
var_dump(pinba_timer_add([0 => "x"], 1.0));

// pinba_timer_add clamps a negative value to 0 but still creates the timer,
// unlike PinbaClient::addTimer below which rejects it.
$timer = pinba_timer_add(["op" => "x"], -1.0);
var_dump(get_resource_type($timer));
var_dump(pinba_timer_get_info($timer)["value"]);

var_dump(pinba_tag_set("", "v"));

// Client API rejects the same invalid inputs.
$client = new PinbaClient(["127.0.0.1:1"]);
var_dump($client->setRusage([1.0]));
var_dump($client->addTimer([], 1.0));
var_dump($client->addTimer(["a" => "b"], -1.0));
var_dump($client->addTimer(["a" => "b"], 1.0, [1.0]));
?>
--EXPECT--
pinba_timer_start(): tags array cannot be empty
bool(false)
pinba_timer_start(): hit_count must be greater than 0 (0 was passed)
bool(false)
pinba_timer_add(): tags can only have string names (i.e. tags array cannot contain numeric indexes)
bool(false)
pinba_timer_add(): negative time value passed (-1.000000), changing it to 0
string(11) "pinba timer"
float(0)
pinba_tag_set(): tag name cannot be empty
bool(false)
PinbaClient::setRusage(): rusage array must contain exactly 2 elements
bool(false)
PinbaClient::addTimer(): timer tags array cannot be empty
bool(false)
PinbaClient::addTimer(): timer value cannot be less than 0
bool(false)
PinbaClient::addTimer(): rusage array must contain exactly 2 elements
bool(false)
