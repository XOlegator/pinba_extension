#!/usr/bin/env bash
# Build the php-pinba source RPM (and optionally the binary RPMs) from a clean
# checkout, so local runs and CI share one recipe (mirrors tools/run-tests.sh and
# the Debian track's build logic living in scripts, not inline YAML).
#
#   rpm/build-srpm.sh <version> [--rpms]
#
#     <version>   upstream version to stamp into the spec + tarball, e.g. 1.3.1
#     --rpms      also build binary RPMs (needs the php<XY>-php-devel build deps);
#                 without it only the .src.rpm is produced
#
# Requires (Fedora/EL): rpm-build, git, tar, gzip; with --rpms also dnf-plugins-core
# and a Remi-enabled environment for the php<XY>-php-devel BuildRequires.
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

if [ "$mode" = "--rpms" ]; then
    dnf -y builddep "$top/SPECS/pinba.spec"
    rpmbuild --define "_topdir $top" -ba "$top/SPECS/pinba.spec"
else
    rpmbuild --define "_topdir $top" -bs "$top/SPECS/pinba.spec"
fi

echo "TOPDIR=$top"
find "$top/SRPMS" "$top/RPMS" -name '*.rpm' 2>/dev/null | sort
