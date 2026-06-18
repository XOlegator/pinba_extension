--TEST--
Configuring more than PINBA_COLLECTORS_MAX (8) collectors warns and keeps the first 8
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

// INI path: nine collectors, only the first eight are kept. The directive must
// still be accepted (not silently rejected) and reflect the configured value.
$nine = implode(",", array_map(fn($i) => "s$i.example:30002", range(1, 9)));
var_dump(ini_set("pinba.server", $nine) !== false);
var_dump(ini_get("pinba.server") === $nine);

// Eight collectors fit exactly and must not warn.
$eight = implode(",", array_map(fn($i) => "s$i.example:30002", range(1, 8)));
var_dump(ini_set("pinba.server", $eight) !== false);

// PinbaClient path: same overflow handling on the constructor.
$client = new PinbaClient(array_map(fn($i) => "s$i.example:30002", range(1, 9)));
var_dump($client instanceof PinbaClient);

// Clear the configured collectors so request shutdown does not try to resolve them.
ini_set("pinba.server", "");
?>
--EXPECT--
ini_set(): pinba.server: more than 8 collectors specified, ignoring the rest
bool(true)
bool(true)
bool(true)
PinbaClient::__construct(): PinbaClient: more than 8 collectors specified, ignoring the rest
bool(true)
