#!/usr/bin/env bash
# Build the php-pinba source RPM (and optionally the binary RPMs) from a clean
# checkout, so local runs and CI share one recipe (mirrors tools/run-tests.sh and
# the Debian track's build logic living in scripts, not inline YAML).
#
#   rpm/build-srpm.sh <version> [--rpms]
#
#     <version>   upstream version to stamp into the spec + tarball, e.g. 1.3.1
#     --rpms      also build the binary RPM (needs php-devel/php-cli/protobuf-c-devel,
#                 all in base Fedora/EL); without it only the .src.rpm is produced
#
# Requires (Fedora/EL): rpm-build, git, tar, gzip; with --rpms also dnf-plugins-core.
# The extension builds against the distro-native PHP, so no Remi is needed.
set -euo pipefail

ver="${1:?usage: build-srpm.sh <version> [--rpms]}"
mode="${2:-}"
root="$(cd "$(dirname "$0")/.." && pwd)"

top="$(mktemp -d)"
mkdir -p "$top"/{SOURCES,SPECS,BUILD,RPMS,SRPMS}

# Stamp the requested version into a working copy of the spec.
sed "s/^Version:.*/Version:        ${ver}/" "$root/rpm/pinba.spec" > "$top/SPECS/pinba.spec"

# Pristine source tarball, unpacking to pinba-<ver>/ as %autosetup expects.
git -c safe.directory='*' -C "$root" archive --prefix="pinba-${ver}/" HEAD \
    | gzip -9 > "$top/SOURCES/pinba-${ver}.tar.gz"

# Build noise goes to stderr so stdout carries only the artifact paths, which
# callers (the publish workflow) parse to find the .src.rpm.
if [ "$mode" = "--rpms" ]; then
    dnf -y builddep "$top/SPECS/pinba.spec" >&2
    rpmbuild --define "_topdir $top" -ba "$top/SPECS/pinba.spec" >&2
else
    rpmbuild --define "_topdir $top" -bs "$top/SPECS/pinba.spec" >&2
fi

echo "TOPDIR=$top" >&2
find "$top/SRPMS" "$top/RPMS" -name '*.rpm' 2>/dev/null | sort
