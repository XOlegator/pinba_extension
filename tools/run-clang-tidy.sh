#!/usr/bin/env bash
#
# Run clang-tidy on the hand-written C of the extension (pinba.c and, via the
# header filter in .clang-tidy, php_pinba.h). The generated protobuf-c bindings
# (pinba-pb-c.c / pinba.pb-c.h) are never analyzed.
#
# Requires `phpize && ./configure --enable-pinba` to have produced config.h, plus
# php-config, pkg-config and libprotobuf-c development headers.
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

if [[ ! -f config.h ]]; then
  echo "config.h is missing; run 'phpize && ./configure --enable-pinba' first." >&2
  exit 1
fi

php_includes="$(php-config --includes)"
protobuf_c_cflags="$(pkg-config --cflags libprotobuf-c 2>/dev/null || true)"

# Word-splitting of the include flags is intended.
# shellcheck disable=SC2086
exec clang-tidy pinba.c -- \
  -std=gnu11 -D_GNU_SOURCE -DHAVE_CONFIG_H -include config.h -DNDEBUG -I. \
  $php_includes $protobuf_c_cflags
