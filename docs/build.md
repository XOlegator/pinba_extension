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

- build on `PHP 8.2`
- build on `PHP 8.3`
- build on `PHP 8.4`
- build on `PHP 8.5`
- PHPT execution on the same matrix
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
