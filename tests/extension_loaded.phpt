--TEST--
Pinba extension exposes its baseline API
--SKIPIF--
<?php if (!extension_loaded("pinba")) print "skip"; ?>
--FILE--
<?php
var_dump(extension_loaded("pinba"));
var_dump(function_exists("pinba_get_info"));
var_dump(function_exists("pinba_get_data"));
var_dump(function_exists("pinba_timer_start"));
var_dump(class_exists("PinbaClient"));
var_dump(defined("PINBA_AUTO_FLUSH"));
var_dump(defined("PINBA_FLUSH_RESET_DATA"));
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
