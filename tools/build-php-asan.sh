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
# Build dependencies (Debian/Ubuntu): a C toolchain, make, curl, pkg-config.
set -euo pipefail

SERIES="${1:-8.4}"
PREFIX="${PREFIX:-$HOME/php-asan}"
SRC_ROOT="${SRC_ROOT:-$HOME/php-asan-src}"
SAN_FLAGS="-fsanitize=address,undefined -fno-omit-frame-pointer -fno-sanitize-recover=undefined -g -O1"

if [ -x "$PREFIX/bin/php" ]; then
    echo "==> ASan PHP already present in $PREFIX"
    "$PREFIX/bin/php" -v
    exit 0
fi

version="$(curl -fsSL "https://www.php.net/releases/index.php?json&version=$SERIES&max=1" \
    | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -1)"
if [ -z "$version" ]; then
    echo "could not resolve the latest PHP $SERIES release" >&2
    exit 1
fi
echo "==> building PHP $version with ASan/UBSan"

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
    CFLAGS="$SAN_FLAGS" \
    LDFLAGS="-fsanitize=address,undefined"
make -j"$(nproc)"
make install

"$PREFIX/bin/php" -v
