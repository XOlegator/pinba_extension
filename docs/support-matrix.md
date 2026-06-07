# Support Matrix

This document records the active support policy for the modernization fork of `pinba_extension`.

Status below is aligned with the official PHP supported versions page as checked on 2026-06-07.

## Target Ubuntu Releases

- `Ubuntu 24.04 LTS (noble)`
- `Ubuntu 26.04 LTS (resolute)`

## Target PHP Matrix

| PHP branch | Upstream status on 2026-06-07 | Fork policy |
| --- | --- | --- |
| `8.2` | security support only until `2026-12-31` | keep in build-and-test matrix while upstream still supports it |
| `8.3` | security support only until `2027-12-31` | keep supported |
| `8.4` | active support until `2026-12-31` | keep supported |
| `8.5` | active support until `2027-12-31` | keep supported |

## Out Of Scope PHP Branches

- `PHP 8.1` and older are outside active support for this fork.
- End-of-life PHP branches are not targets for CI, packaging, or security maintenance.

## Policy Notes

- A newly released PHP branch is not considered supported by this fork until it builds and passes
  the regression baseline in CI.
- Discovery of a new PHP branch should trigger a Pull Request that updates CI and packaging
  metadata, rather than changing release automation silently.
- Packaging support may lag build support while Ubuntu packages and Launchpad workflows are being
  finalized.

## Source

- Official PHP lifecycle page:
  [php.net/supported-versions](https://www.php.net/supported-versions.php)
