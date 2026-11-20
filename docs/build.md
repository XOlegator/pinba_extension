# Build and Test

## Supported Development Baseline

The current active development baseline targets these PHP branches:

- `PHP 8.2`
- `PHP 8.3`
- `PHP 8.4`
- `PHP 8.5`
- `PHP 8.6`

CI also covers PHP 8.0 and 8.1 for Enterprise Linux 9 compatibility.

The machine-readable source of truth for that matrix is:

- `.github/php-versions.json`

## Build Dependencies

The extension links against the system **protobuf-c** runtime. A normal build
requires `libprotobuf-c-dev` (version 1.3.0 or later). On Debian and Ubuntu:

```bash
sudo apt-get install -y libprotobuf-c-dev
```

The C bindings `pinba-pb-c.c` and `pinba.pb-c.h` are committed to the repository.
To regenerate them, use `protoc` from `protobuf-compiler` and the `protoc-gen-c`
1.5.1 plugin from `protobuf-c-compiler` on Ubuntu 26.04:

```bash
protoc --c_out=. pinba.proto
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

## Troubleshooting

### `./configure` reports `sed: No such file or directory` or `sort: No such file or directory`

This is a local environment problem, not a repository one. `configure` blanks
shell variables whose value contains a newline, so an empty or newline-bearing
entry in `PATH` can transiently drop core tools like `sed`/`sort` from the
lookup path during configuration. Sanitize `PATH` before running
`phpize`/`configure`:

```bash
printf '%s\n' "$PATH" | tr ':' '\n' | grep -n '^$'    # spot empty PATH entries
export PATH="$(printf '%s' "$PATH" | tr -s ':' | sed 's/^://; s/:$//')"
```

Then re-run `phpize && ./configure --enable-pinba`.

## Testing

The extension is tested with the standard PHP `.phpt` harness (`run-tests.php`),
the canonical test format for C extensions. Run the whole suite with:

```bash
make test
```

Run a single test:

```bash
make test NO_INTERACTION=1 REPORT_EXIT_STATUS=1 TESTS=tests/ini_set.phpt
```

`tools/run-tests.sh` wraps the build-and-test cycle so local runs and CI share
one recipe. It rebuilds the extension with the flags appropriate for each mode
and always writes a JUnit report to `tests/junit.xml`:

```bash
tools/run-tests.sh test       # plain optimized build (default)
tools/run-tests.sh coverage   # gcov-instrumented build; writes coverage.xml (needs gcovr)
tools/run-tests.sh asan       # AddressSanitizer + UBSan build
tools/run-tests.sh valgrind   # run each test under Valgrind memcheck (needs valgrind)
```

## CI Matrix

GitHub Actions currently validates:

- required builds and PHPT tests on PHP 8.0–8.6
- experimental, non-blocking build and PHPT tests on PHP 8.7 nightly
- C code coverage (gcov/gcovr) uploaded to Codecov
- AddressSanitizer + UBSan run of the PHPT suite against an ASan-instrumented PHP
- a thread-safe (ZTS) build, run against the PHPT suite under ASan/UBSan
- Valgrind memcheck run (informational, non-blocking)
- CodeQL static security analysis of the C code

The sanitizer job builds PHP itself with ASan/UBSan (via `tools/build-php-asan.sh`,
cached between runs) so the instrumented extension is not loaded into a stock PHP
— a stock PHP uses `RTLD_DEEPBIND`, which ASan rejects. The Valgrind job stays
informational because PHP is noisy under Valgrind until a suppression file is
curated.

`tools/run-tests.sh asan` works locally too: it preloads the ASan runtime, which
is fine on a PHP built without `RTLD_DEEPBIND` (typical local dev builds), and
skips the preload when run against an instrumented PHP (`PHP_ASAN=1`).

The CI workflow reads the PHP version list directly from `.github/php-versions.json`.

## Notes

- The extension contract and wire behavior must remain stable while build, CI, and
  packaging infrastructure are modernized.
