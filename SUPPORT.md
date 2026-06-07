# Support

## What This Repository Supports

This fork supports:

- active development of the Pinba PHP extension;
- build and test validation for the supported PHP matrix;
- modernization of CI, packaging, and release engineering;
- compatibility with the Pinba stack contract.

The current public matrix is documented in [docs/support-matrix.md](docs/support-matrix.md).

## What To Use For Questions

- Usage and development questions: open a GitHub Discussion or Issue in this repository.
- Confirmed bugs: open a bug report.
- Feature proposals: open a feature request.
- Security reports: use the private process in [SECURITY.md](SECURITY.md).

## What Maintainers Need In Reports

- PHP version and SAPI;
- OS and distribution release;
- exact extension commit, branch, or release tag;
- reproduction steps;
- expected behavior;
- actual behavior;
- whether the problem also reproduces with Pinba Engine.

## Support Boundaries

- End-of-life PHP versions are outside active support.
- Legacy PECL/PEAR metadata is kept only for historical context.
- Packaging behavior may differ by Ubuntu suite and PHP ABI until the Debian/Launchpad layer is
  fully finalized.
