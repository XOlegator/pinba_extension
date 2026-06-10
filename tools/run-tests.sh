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
    # Leak detection is left to the Valgrind mode (PHP leaks at shutdown). The
    # alloc/dealloc/ODR mismatch checks only fire at the instrumented/uninstru-
    # mented boundary, so they are tolerated while overflows, UAF and UB still
    # fail the run.
    asan_env=(
        "USE_ZEND_ALLOC=0"
        "ASAN_OPTIONS=detect_leaks=0:verify_asan_link_order=0:alloc_dealloc_mismatch=0:new_delete_type_mismatch=0:detect_odr_violation=0:abort_on_error=1"
        "UBSAN_OPTIONS=print_stacktrace=1:halt_on_error=1"
    )
    if [ "${PHP_ASAN:-0}" != "1" ]; then
        # PHP is not instrumented: preload the ASan runtime so the extension's
        # instrumentation has a runtime. Only works on a PHP built without
        # RTLD_DEEPBIND; CI instead builds an instrumented PHP and sets PHP_ASAN.
        asan_env+=("LD_PRELOAD=$(gcc -print-file-name=libasan.so)")
    fi
    env "${asan_env[@]}" make test TEST_PHP_ARGS="$test_php_args"
else
    make test TEST_PHP_ARGS="$test_php_args"
fi

if [ "$MODE" = "coverage" ]; then
    echo "==> [coverage] generating coverage.xml"
    # ./configure compiles throwaway conftest probes; with coverage flags they
    # leave orphan .gcno files with no source that would break gcovr.
    rm -f "$ROOT"/a-conftest.* "$ROOT"/conftest.*
    gcovr --root "$ROOT" --filter "$ROOT/pinba\.c" \
        --gcov-ignore-errors=no_working_dir_found \
        --xml-pretty --output "$ROOT/coverage.xml" --txt
fi
