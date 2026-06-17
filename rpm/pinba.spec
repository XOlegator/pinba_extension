# RPM spec for the Pinba PHP extension. One SRPM produces two package families:
#
#   php-pinba              the main package, built against the distro-native PHP
#                          (Fedora base repos / EL AppStream) — no Remi needed.
#   php<XY>-php-pinba       one subpackage per PHP version listed in php_versions,
#                          built against Remi's SCL php<XY> stacks.
#
# The same SRPM is submitted to every Copr chroot; on each chroot the base build
# picks up that OS's own PHP (Fedora 43->8.4, 44->8.5, EL9->8.1, EL10->8.3) while
# the SCL subpackages cover the parallel-installable Remi collections.
#
# Native PHP layout (from `php-config`):
#   php-config / phpize : /usr/bin/
#   extension_dir       : /usr/lib64/php/modules
#   scan dir for .ini   : /etc/php.d
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
Version:        1.3.2
Release:        1%{?dist}
Summary:        Pinba metrics extension for the distro-native PHP

License:        LGPL-2.1-or-later
URL:            https://github.com/XOlegator/pinba_extension
Source0:        %{url}/releases/download/v%{version}/pinba-%{version}.tar.gz

# Build deps for the base build against the OS's own PHP. php-cli is needed for
# the %%check below; on EL the php-devel/php-cli come from the AppStream php
# module stream (php:8.1 on EL9), which the Copr chroot must have enabled.
BuildRequires:  gcc
BuildRequires:  make
BuildRequires:  protobuf-c-devel
BuildRequires:  php-devel
BuildRequires:  php-cli
%{lua:
for v in string.gmatch(rpm.expand("%{php_versions}"), "%S+") do
  print("BuildRequires:  php"..v.."-php-devel\n")
end
}

Requires:       php-common%{?_isa}

%description
Pinba is a PHP extension that collects per-request and per-timer metrics and
ships them over UDP (protobuf) to a Pinba server for aggregation. This package
builds the extension for the distribution's own PHP; the parallel-installable
php<XY>-php-pinba subpackages cover the Remi software collections.

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

# Base build against the distro-native PHP (/usr/bin), from a pristine copy.
rm -rf "../bv-base"
cp -a "$src" "../bv-base"
pushd "../bv-base"
    /usr/bin/phpize
    ./configure --with-php-config=/usr/bin/php-config
    %make_build
popd

# One build per Remi SCL; phpize/configure artifacts differ per PHP and must
# not collide, so each version is built from its own pristine copy.
for v in %{php_versions}; do
    scl="/opt/remi/php${v}/root"
    rm -rf "../bv-${v}"
    cp -a "$src" "../bv-${v}"
    pushd "../bv-${v}"
        "${scl}/usr/bin/phpize"
        ./configure --with-php-config="${scl}/usr/bin/php-config"
        %make_build
    popd
done

%install
# Base build -> distro-native extension dir + /etc/php.d scan dir.
base_extdir="$(/usr/bin/php-config --extension-dir)"
install -Dpm 0755 "../bv-base/modules/pinba.so" "%{buildroot}${base_extdir}/pinba.so"
install -dm 0755 "%{buildroot}/etc/php.d"
cat > "%{buildroot}/etc/php.d/40-pinba.ini" <<EOF
; Enable the Pinba extension
extension=pinba.so
EOF

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
base_extdir="$(/usr/bin/php-config --extension-dir)"
/usr/bin/php -n -d extension="%{buildroot}${base_extdir}/pinba.so" -m | grep -qx pinba
for v in %{php_versions}; do
    scl="/opt/remi/php${v}/root"
    extdir="$("${scl}/usr/bin/php-config" --extension-dir)"
    "${scl}/usr/bin/php" -n -d extension="%{buildroot}${extdir}/pinba.so" -m | grep -qx pinba
done

# --- base package file list --------------------------------------------------
%files
%license COPYING
%doc README.md
%{_libdir}/php/modules/pinba.so
%config(noreplace) /etc/php.d/40-pinba.ini

# --- per-version (Remi SCL) file lists ---------------------------------------
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
* Mon Jun 16 2026 Oleg Ekhlakov <o.ekhlakov@protonmail.com> - 1.3.2-1
- Add the base php-pinba package built against the distro-native PHP
  (Fedora base repos / EL AppStream), alongside the Remi SCL subpackages.

* Sun Jun 14 2026 Oleg Ekhlakov <o.ekhlakov@protonmail.com> - 1.3.1-1
- Initial RPM packaging for the Remi php82..php85 SCLs.
