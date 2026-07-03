# Support Matrix

This document records the active support policy for the modernization fork of `pinba_extension`.

Status below is aligned with the official PHP supported versions page as checked on 2026-06-07.

## Target Ubuntu Releases

- `Ubuntu 24.04 LTS (noble)`
- `Ubuntu 26.04 LTS (resolute)`

## Support Policy

Support is defined by **operating-system lifecycle**, not only by upstream PHP end-of-life:

> We support the PHP version that ships as the default on every operating system we target, for as
> long as that OS is supported by its vendor, plus every upstream-active PHP branch. We never
> require users to enable a non-default module stream to install the extension.

The **minimum supported PHP is 8.0** — the default (non-modular) PHP on AlmaLinux/RHEL 9, which
its vendor keeps patched for the OS lifetime.

## Tier A — upstream-active branches

These follow the official PHP lifecycle and form the primary build-and-test matrix. They are also
the branches packaged for Ubuntu (`php<ver>-pinba`).

| PHP branch | Upstream status on 2026-06-07 |
| --- | --- |
| `8.2` | security support only until `2026-12-31` |
| `8.3` | security support only until `2027-12-31` |
| `8.4` | active support until `2026-12-31` |
| `8.5` | active support until `2027-12-31` |

## Tier B — distro-native branches on supported operating systems

Upstream-EOL, but shipped and security-backported by a still-supported OS, so we support them for
that OS's lifetime. They are built and load-tested (and kept in the CI build-and-test matrix via
`distro_native_floor` in `.github/php-versions.json`), but we do not chase upstream fixes for them.

| PHP branch | Where it is the default | Notes |
| --- | --- | --- |
| `8.0` | AlmaLinux / RHEL 9 (non-modular base) | minimum supported; `dnf install php` → 8.0, no module needed |
| `8.1` | available on EL9 as a module stream | built and tested so the EL9 opt-in stream is covered |

## Out Of Scope PHP Branches

- PHP branches that are both upstream-EOL **and** not the default on any OS we still target.
- `PHP 8.0` and older will drop out of scope when Enterprise Linux 9 reaches end of maintenance.

## Policy Notes

- A newly released PHP branch is not considered supported by this fork until it builds and passes
  the regression baseline in CI.
- Discovery of a new PHP branch should trigger a Pull Request that updates CI and packaging
  metadata, rather than changing release automation silently.
- Packaging support may lag build support while Ubuntu packages and Launchpad workflows are being
  finalized.
- Scheduled automation refreshes `.github/php-versions.json`, but merge review is still required
  before the fork treats a discovered branch as active support.

## Source

- Official PHP lifecycle page:
  [php.net/supported-versions](https://www.php.net/supported-versions.php)
- Repository matrix metadata:
  `.github/php-versions.json`
- Packaging target metadata:
  `.github/packaging-matrix.json`
