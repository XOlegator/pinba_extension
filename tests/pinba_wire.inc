<?php
// Shared helpers for Pinba wire-format tests. This file is an include, not a
// test on its own (run-tests only picks up *.phpt).
//
// The Pinba packet is a proto2 Pinba.Request message. Tag names and values are
// de-duplicated into a string dictionary (field 15); request-level tags
// (fields 20/21) and per-timer tags (fields 12/13/14) reference entries in that
// dictionary by their 0-based index. These helpers decode a packet and resolve
// those indices so tests can assert the logical content without depending on
// the internal dictionary ordering.

// Minimal top-level protobuf scanner returning [field_number => [values...]].
// Wire type 0 (varint) -> int, 1 (64-bit) -> little-endian double,
// 2 (length-delimited) -> raw bytes, 5 (32-bit) -> little-endian float.
function pinba_wire_decode(string $data): array
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
            $fields[$field][] = unpack("e", substr($data, $pos, 8))[1];
            $pos += 8;
        } elseif ($wire === 2) {
            $length = $read_varint();
            $fields[$field][] = substr($data, $pos, $length);
            $pos += $length;
        } elseif ($wire === 5) {
            $fields[$field][] = unpack("g", substr($data, $pos, 4))[1];
            $pos += 4;
        } else {
            break;
        }
    }
    return $fields;
}

// Resolve request-level tags (fields 20/21) into a sorted name => value map.
function pinba_wire_request_tags(array $fields): array
{
    $dict = $fields[15] ?? [];
    $names = $fields[20] ?? [];
    $values = $fields[21] ?? [];
    $out = [];
    foreach ($names as $i => $name_idx) {
        $out[$dict[$name_idx]] = $dict[$values[$i]];
    }
    ksort($out);
    return $out;
}

// Resolve per-timer tags into a list (one sorted name => value map per timer),
// splitting the flat tag arrays (fields 13/14) by timer_tag_count (field 12).
function pinba_wire_timer_tags(array $fields): array
{
    $dict = $fields[15] ?? [];
    $counts = $fields[12] ?? [];
    $names = $fields[13] ?? [];
    $values = $fields[14] ?? [];
    $out = [];
    $offset = 0;
    foreach ($counts as $count) {
        $map = [];
        for ($i = 0; $i < $count; $i++) {
            $map[$dict[$names[$offset + $i]]] = $dict[$values[$offset + $i]];
        }
        ksort($map);
        $out[] = $map;
        $offset += $count;
    }
    return $out;
}
