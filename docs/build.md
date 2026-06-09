# Build and Test

## Supported Development Baseline

The current active development baseline targets these PHP branches:

- `PHP 8.2`
- `PHP 8.3`
- `PHP 8.4`
- `PHP 8.5`

Older PHP branches are not part of the active support matrix for this fork.

The machine-readable source of truth for that matrix is:

- `.github/php-versions.json`

## Build Dependencies

The extension links against the system **protobuf-c** runtime (it is no longer
vendored). Required at build time:

- `libprotobuf-c-dev` (headers + `pkg-config` entry, `>= 1.0.0`)
- `protobuf-c-compiler` (`protoc-c`) — only needed to regenerate the bindings,
  not for a normal build

At runtime the shared library `libprotobuf-c1` is required.

On Debian/Ubuntu:

```bash
sudo apt-get install -y libprotobuf-c-dev protobuf-c-compiler
```

The C bindings `pinba-pb-c.c` / `pinba.pb-c.h` are generated from `pinba.proto`
and committed to the repository, so a normal build does not invoke `protoc-c`.
To regenerate them after changing `pinba.proto`:

```bash
protoc-c --c_out=. pinba.proto
mv pinba.pb-c.c pinba-pb-c.c   # match the filename used by config.m4
```

`pinba.proto` is a stable wire contract shared with the Pinba Engine; only
append fields, never renumber or retype existing ones.

## Local Build

This project remains a standard PHP extension and is built with `phpize`.

Minimal local flow:

```bash
phpize
./configure --enable-pinba
make -j"$(nproc)"
make test
```

If multiple PHP versions are installed locally, always pair `phpize` and `php-config`
from the same PHP version:

```bash
phpize8.4
./configure --with-php-config=/usr/bin/php-config8.4 --enable-pinba
make -j"$(nproc)"
make test
```

## Smoke Test

Current smoke coverage includes:

- `tests/ini_set.phpt`

Run only the smoke test:

```bash
make test NO_INTERACTION=1 REPORT_EXIT_STATUS=1 TESTS=tests/ini_set.phpt
```

## CI Matrix

GitHub Actions currently validates:

- build on `PHP 8.2`
- build on `PHP 8.3`
- build on `PHP 8.4`
- build on `PHP 8.5`
- PHPT execution on the same matrix

The CI workflow reads the PHP version list directly from `.github/php-versions.json`.

## Notes

- The extension contract and wire behavior must remain stable while build, CI, and
  packaging infrastructure are modernized.
- `package.xml` is kept as historical legacy metadata and is not the active source of truth
  for fork development.
