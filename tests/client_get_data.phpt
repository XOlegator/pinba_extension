--TEST--
PinbaClient can build a protobuf payload without network I/O
--SKIPIF--
<?php if (!extension_loaded("pinba")) print "skip"; ?>
--FILE--
<?php
$client = new PinbaClient(["127.0.0.1:30002"]);

var_dump($client instanceof PinbaClient);
var_dump($client->setHostname("worker-1"));
var_dump($client->setServername("frontend"));
var_dump($client->setScriptname("/health"));
var_dump($client->setSchema("https"));
var_dump($client->setRequestCount(7));
var_dump($client->setDocumentSize(1234));
var_dump($client->setMemoryPeak(5678));
var_dump($client->setMemoryFootprint(4321));
var_dump($client->setRequestTime(0.5));
var_dump($client->setStatus(200));
var_dump($client->setTag("env", "prod"));
var_dump($client->addTimer(["kind" => "db"], 0.75, [0.01, 0.02]));

$data = $client->getData();
var_dump(is_string($data));
var_dump(strlen($data) > 0);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
