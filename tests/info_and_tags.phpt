--TEST--
Pinba request info and request tags can be managed from userland
--SKIPIF--
<?php if (!extension_loaded("pinba")) print "skip"; ?>
--FILE--
<?php
pinba_reset();

var_dump(pinba_hostname_set("worker-1"));
var_dump(pinba_server_name_set("frontend"));
var_dump(pinba_script_name_set("/status"));
var_dump(pinba_schema_set("https"));
var_dump(pinba_request_time_set(1.25));
var_dump(pinba_tag_set("env", "prod"));
var_dump(pinba_tag_set("region", "eu"));

$info = pinba_get_info();
ksort($info["tags"]);

var_export([
    "hostname" => $info["hostname"],
    "server_name" => $info["server_name"],
    "script_name" => $info["script_name"],
    "schema" => $info["schema"],
    "req_time" => $info["req_time"],
    "tags" => $info["tags"],
]);
echo "\n";

var_export(pinba_tag_get("env"));
echo "\n";
var_export(pinba_tag_delete("env"));
echo "\n";
var_export(pinba_tag_get("env"));
echo "\n";

$tags = pinba_tags_get();
ksort($tags);
var_export($tags);
echo "\n";
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
array (
  'hostname' => 'worker-1',
  'server_name' => 'frontend',
  'script_name' => '/status',
  'schema' => 'https',
  'req_time' => 1.25,
  'tags' => 
  array (
    'env' => 'prod',
    'region' => 'eu',
  ),
)
'prod'
true
false
array (
  'region' => 'eu',
)
