# Build and Test

## Supported Development Baseline

The current active development baseline targets these PHP branches:

- `PHP 8.2`
- `PHP 8.3`
- `PHP 8.4`
- `PHP 8.5`

Older PHP branches are not part of the active support matrix for this fork.

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

## Notes

- The extension contract and wire behavior must remain stable while build, CI, and
  packaging infrastructure are modernized.
- `package.xml` is kept as historical legacy metadata and is not the active source of truth
  for fork development.
