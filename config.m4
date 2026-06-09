dnl config.m4 for the pinba extension

PHP_ARG_ENABLE([pinba], [for Pinba support],
[  --enable-pinba[=DIR]         Include Pinba support.])

if test "$PHP_PINBA" != "no"; then

  AC_CHECK_HEADERS([malloc.h])
  PHP_CHECK_FUNC(mallinfo)

  dnl Pinba serializes packets with the protobuf-c runtime. The runtime is no
  dnl longer vendored in this repository; link against the system libprotobuf-c
  dnl (>= 1.0.0). The C bindings in pinba-pb-c.c / pinba.pb-c.h are generated
  dnl from pinba.proto with protoc-gen-c and include <protobuf-c/protobuf-c.h>.
  AC_PATH_PROG(PKG_CONFIG, pkg-config, no)
  if test "$PKG_CONFIG" != "no" && $PKG_CONFIG --atleast-version=1.0.0 libprotobuf-c; then
    PROTOBUF_C_CFLAGS=`$PKG_CONFIG --cflags libprotobuf-c`
    PROTOBUF_C_LIBS=`$PKG_CONFIG --libs libprotobuf-c`
  else
    AC_MSG_WARN([pkg-config entry for libprotobuf-c not found, falling back to -lprotobuf-c])
    PROTOBUF_C_CFLAGS=""
    PROTOBUF_C_LIBS="-lprotobuf-c"
  fi

  PHP_CHECK_LIBRARY(protobuf-c, protobuf_c_message_pack_to_buffer,
  [
    PHP_EVAL_INCLINE([$PROTOBUF_C_CFLAGS])
    PHP_EVAL_LIBLINE([$PROTOBUF_C_LIBS], [PINBA_SHARED_LIBADD])
  ], [
    AC_MSG_ERROR([libprotobuf-c not found. Install libprotobuf-c-dev (Debian/Ubuntu) or protobuf-c-devel (Fedora/RHEL).])
  ], [$PROTOBUF_C_LIBS])

  PHP_SUBST(PINBA_SHARED_LIBADD)

  PHP_NEW_EXTENSION(pinba, pinba-pb-c.c pinba.c, $ext_shared,, -DNDEBUG)
fi
