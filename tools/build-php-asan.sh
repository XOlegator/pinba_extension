#!/usr/bin/env bash
# Build a minimal PHP CLI instrumented with AddressSanitizer + UBSan from an
# official release tarball (which ships pre-generated parsers, so re2c/bison/
# autoconf are not required).
#
# Loading an ASan-instrumented extension into a stock PHP fails because PHP uses
# RTLD_DEEPBIND, which ASan rejects. Instrumenting PHP itself avoids that and
# gives a real, gating sanitizer run. Installed into PREFIX and cache-friendly:
# skipped when PREFIX already holds a php binary.
#
#   tools/build-php-asan.sh [series]   # e.g. 8.4 (default)
#
# Set PHP_ZTS=1 to build a thread-safe (ZTS) PHP as well. ondrej/php on Linux
# ships NTS only, so a from-source build is the only way to exercise the
# extension's thread-safety claim (composer.json declares support-zts: true).
#
# Build dependencies (Debian/Ubuntu): a C toolchain, make, curl, pkg-config.
set -euo pipefail

SERIES="${1:-8.4}"
PHP_ZTS="${PHP_ZTS:-0}"
if [ "$PHP_ZTS" = "1" ]; then
    PREFIX="${PREFIX:-$HOME/php-zts}"
    SRC_ROOT="${SRC_ROOT:-$HOME/php-zts-src}"
    flavor="ZTS+ASan"
else
    PREFIX="${PREFIX:-$HOME/php-asan}"
    SRC_ROOT="${SRC_ROOT:-$HOME/php-asan-src}"
    flavor="ASan"
fi
SAN_FLAGS="-fsanitize=address,undefined -fno-omit-frame-pointer -fno-sanitize-recover=undefined -g -O1"

if [ -x "$PREFIX/bin/php" ]; then
    echo "==> $flavor PHP already present in $PREFIX"
    "$PREFIX/bin/php" -v
    exit 0
fi

version="$(curl -fsSL "https://www.php.net/releases/index.php?json&version=$SERIES&max=1" \
    | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -1)"
if [ -z "$version" ]; then
    echo "could not resolve the latest PHP $SERIES release" >&2
    exit 1
fi
echo "==> building PHP $version with $flavor"

zts_args=()
if [ "$PHP_ZTS" = "1" ]; then
    zts_args+=(--enable-zts)
fi

mkdir -p "$SRC_ROOT"
cd "$SRC_ROOT"
tarball="php-$version.tar.gz"
[ -f "$tarball" ] || curl -fsSL -o "$tarball" "https://www.php.net/distributions/$tarball"
rm -rf "php-$version"
tar -xzf "$tarball"
cd "php-$version"

./configure \
    --prefix="$PREFIX" \
    --disable-all \
    --enable-cli \
    --enable-debug \
    --with-pic \
    "${zts_args[@]}" \
    CFLAGS="$SAN_FLAGS" \
    LDFLAGS="-fsanitize=address,undefined"
make -j"$(nproc)"
make install

"$PREFIX/bin/php" -v
