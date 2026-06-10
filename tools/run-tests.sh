#!/usr/bin/env bash
# Unified test entry point for the Pinba PHP extension.
#
# Rebuilds the extension with the flags appropriate for MODE and runs the PHPT
# suite, so local runs and CI exercise the exact same build/test recipe.
#
#   tools/run-tests.sh [test|coverage|asan|valgrind]
#
#   test      plain optimized build (default)
#   coverage  gcov-instrumented build; writes coverage.xml (Cobertura) via gcovr
#   asan      AddressSanitizer + UBSan build, run with the libasan runtime
#   valgrind  plain build, run each test under Valgrind's memcheck (run-tests -m)
#
# A JUnit report is always written to tests/junit.xml (override TEST_PHP_JUNIT).
set -euo pipefail

MODE="${1:-test}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

export NO_INTERACTION=1
export REPORT_EXIT_STATUS=1
export TEST_PHP_JUNIT="${TEST_PHP_JUNIT:-$ROOT/tests/junit.xml}"

cflags="-O2 -g"
test_php_args=""

case "$MODE" in
    test) ;;
    coverage)
        cflags="-O0 -g --coverage"
        ;;
    asan)
        cflags="-O1 -g -fno-omit-frame-pointer -fsanitize=address,undefined -fno-sanitize-recover=undefined"
        ;;
    valgrind)
        test_php_args="-m"
        ;;
    *)
        echo "Unknown mode: $MODE (use: test | coverage | asan | valgrind)" >&2
        exit 2
        ;;
esac

echo "==> [$MODE] building extension"
make clean >/dev/null 2>&1 || true
phpize --clean >/dev/null 2>&1 || true
phpize >/dev/null
./configure --enable-pinba CFLAGS="$cflags" >/dev/null
make -j"$(nproc)"

echo "==> [$MODE] running PHPT suite"
if [ "$MODE" = "asan" ]; then
    # The extension is instrumented but PHP itself is not, so preload the ASan
    # runtime and let PHP use the system allocator. PHP leaks at shutdown by
    # design, so leak detection is left to the Valgrind mode.
    USE_ZEND_ALLOC=0 \
    LD_PRELOAD="$(gcc -print-file-name=libasan.so)" \
    ASAN_OPTIONS="detect_leaks=0:verify_asan_link_order=0:abort_on_error=1" \
    UBSAN_OPTIONS="print_stacktrace=1:halt_on_error=1" \
    make test TEST_PHP_ARGS="$test_php_args"
else
    make test TEST_PHP_ARGS="$test_php_args"
fi

if [ "$MODE" = "coverage" ]; then
    echo "==> [coverage] generating coverage.xml"
    gcovr --root "$ROOT" --filter "$ROOT/pinba\.c" \
        --xml-pretty --output "$ROOT/coverage.xml" --txt
fi
