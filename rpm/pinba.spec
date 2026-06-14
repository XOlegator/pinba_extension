# RPM spec for the Pinba PHP extension, targeting Remi's SCL php<XY> stacks.
#
# One SRPM produces one php<XY>-php-pinba subpackage per PHP version listed in
# php_versions. The packaging matrix rewrites that single line per build target
# (Fedora / EL) to the versions Remi actually ships there — mirroring how the
# Debian track regenerates control's Build-Depends per suite.
#
# Remi SCL layout (confirmed against rpms.remirepo.net):
#   php-config / phpize : /opt/remi/php<XY>/root/usr/bin/
#   extension_dir       : /opt/remi/php<XY>/root/usr/lib64/php/modules
#   scan dir for .ini   : /etc/opt/remi/php<XY>/php.d

%global php_versions 82 83 84 85

# The extension is built once per SCL in a separate tree, which defeats rpm's
# automatic debugsource collection (it scans a single build dir). Debug symbols
# for a tiny extension are not worth that complexity, so disable the auto
# -debuginfo/-debugsource subpackages.
%global debug_package %{nil}

Name:           php-pinba
Version:        1.3.1
Release:        1%{?dist}
Summary:        Pinba metrics extension for the Remi PHP stacks

License:        LGPL-2.1-or-later
URL:            https://github.com/XOlegator/pinba_extension
Source0:        %{url}/releases/download/v%{version}/pinba-%{version}.tar.gz

BuildRequires:  gcc
BuildRequires:  make
BuildRequires:  protobuf-c-devel
%{lua:
for v in string.gmatch(rpm.expand("%{php_versions}"), "%S+") do
  print("BuildRequires:  php"..v.."-php-devel\n")
end
}

%description
Pinba is a PHP extension that collects per-request and per-timer metrics and
ships them over UDP (protobuf) to a Pinba server for aggregation. This source
package builds the extension for each targeted Remi php<XY> software collection.

# --- one binary subpackage per PHP version -----------------------------------
%{lua:
local isa = rpm.expand("%{?_isa}")
for v in string.gmatch(rpm.expand("%{php_versions}"), "%S+") do
  print("%package -n php"..v.."-php-pinba\n")
  print("Summary:        Pinba metrics extension for the Remi php"..v.." stack\n")
  print("Requires:       php"..v.."-php-common"..isa.."\n")
  print("%description -n php"..v.."-php-pinba\n")
  print("The Pinba PHP extension (per-request and timer metrics over UDP) built for\n")
  print("the Remi php"..v.." software collection.\n\n")
end
}

%prep
%autosetup -n pinba-%{version}

%build
src="$PWD"
for v in %{php_versions}; do
    scl="/opt/remi/php${v}/root"
    # Build each version from a pristine copy; phpize/configure artifacts differ
    # per PHP and must not collide.
    rm -rf "../bv-${v}"
    cp -a "$src" "../bv-${v}"
    pushd "../bv-${v}"
        "${scl}/usr/bin/phpize"
        ./configure --with-php-config="${scl}/usr/bin/php-config"
        %make_build
    popd
done

%install
for v in %{php_versions}; do
    scl="/opt/remi/php${v}/root"
    extdir="$("${scl}/usr/bin/php-config" --extension-dir)"
    install -Dpm 0755 "../bv-${v}/modules/pinba.so" "%{buildroot}${extdir}/pinba.so"
    install -dm 0755 "%{buildroot}/etc/opt/remi/php${v}/php.d"
    cat > "%{buildroot}/etc/opt/remi/php${v}/php.d/40-pinba.ini" <<EOF
; Enable the Pinba extension
extension=pinba.so
EOF
done

%check
for v in %{php_versions}; do
    scl="/opt/remi/php${v}/root"
    extdir="$("${scl}/usr/bin/php-config" --extension-dir)"
    "${scl}/usr/bin/php" -n -d extension="%{buildroot}${extdir}/pinba.so" -m | grep -qx pinba
done

# --- per-version file lists --------------------------------------------------
%{lua:
local libdir = rpm.expand("%{_libdir}")
for v in string.gmatch(rpm.expand("%{php_versions}"), "%S+") do
  print("%files -n php"..v.."-php-pinba\n")
  print("%license COPYING\n")
  print("%doc README.md\n")
  print("/opt/remi/php"..v.."/root"..libdir.."/php/modules/pinba.so\n")
  print("%config(noreplace) /etc/opt/remi/php"..v.."/php.d/40-pinba.ini\n\n")
end
}

%changelog
* Sun Jun 14 2026 Oleg Ekhlakov <o.ekhlakov@protonmail.com> - 1.3.1-1
- Initial RPM packaging for the Remi php82..php85 SCLs.
