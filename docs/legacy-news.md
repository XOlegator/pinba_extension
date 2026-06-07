# Legacy Release Notes

This file preserves the historical Pinba extension release notes from the legacy upstream
project. New fork release notes are generated into `../CHANGELOG.md`.

## Pinba 1.1.0 - 2013-08-17

- Changed extension license to LGPL.

## Pinba 1.0.0 - 2012-08-17

- Fixed PHP 5.4 build.
- Fixed several minor memory leaks.
- Added optional flag to `pinba_flush()` to flush only stopped timers
  (`PINBA_FLUSH_ONLY_STOPPED_TIMERS`).

## Pinba 0.0.6 - 2010-11-26

- Added `pinba_timer_delete()`.
- Added `pinba_hostname_set()`.
- Added experimental IPv6 support.
- Fixed extension crash on empty tag value.

## Pinba 0.0.5 - 2009-10-19

- Added `rusage` to timers.

## Pinba 0.0.4 - 2009-08-26

- Added HTTP response status to response data.
- Added support for Google Protocol Buffers 2.1.0+.
- Added `pinba_script_name_set()`.

## Pinba 0.0.3 - 2009-05-04

- Initial release.
