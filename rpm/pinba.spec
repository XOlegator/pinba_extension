# RPM spec for the Pinba PHP extension, built against the distro-native PHP
# (Fedora base repos / EL AppStream) — no Remi needed. Produces a single
# php-pinba package.
#
# Parallel-installable builds for specific PHP versions (Remi Software
# Collections and modular streams) are provided directly by Remi's own
# repository, which packages this fork upstream since v1.4.0 — so this spec no
# longer ships the php<XY>-php-pinba SCL subpackages. See the README section
# "From the Copr repo" for how the two tracks relate.
#
# Native PHP layout (from `php-config`):
#   php-config / phpize : /usr/bin/
#   extension_dir       : /usr/lib64/php/modules
#   scan dir for .ini   : /etc/php.d

# The extension is a single tiny .so; a -debuginfo/-debugsource pair is not worth
# shipping, so disable the auto debug subpackages.
%global debug_package %{nil}

Name:           php-pinba
Version:        1.5.0
Release:        1%{?dist}
Summary:        Pinba metrics extension for the distro-native PHP

License:        LGPL-2.1-or-later
URL:            https://github.com/XOlegator/pinba_extension
Source0:        %{url}/releases/download/v%{version}/pinba-%{version}.tar.gz

# Build against the OS's own PHP. php-cli is needed for the %%check below; on EL
# the php-devel/php-cli come from the AppStream php module stream (php:8.1 on
# EL9), which the Copr chroot must have enabled.
BuildRequires:  gcc
BuildRequires:  make
BuildRequires:  protobuf-c-devel
BuildRequires:  php-devel
BuildRequires:  php-cli

Requires:       php-common%{?_isa}

%description
Pinba is a PHP extension that collects per-request and per-timer metrics and
ships them over UDP (protobuf) to a Pinba server for aggregation. This package
builds the extension for the distribution's own PHP. For parallel-installable
builds against specific PHP versions, use Remi's repository, which packages this
extension for its module and Software Collection streams.

%prep
%autosetup -n pinba-%{version}

%build
%{_bindir}/phpize
./configure --with-php-config=%{_bindir}/php-config
%make_build

%install
extdir="$(%{_bindir}/php-config --extension-dir)"
install -Dpm 0755 "modules/pinba.so" "%{buildroot}${extdir}/pinba.so"
install -dm 0755 "%{buildroot}/etc/php.d"
cat > "%{buildroot}/etc/php.d/40-pinba.ini" <<EOF
; Enable the Pinba extension
extension=pinba.so
EOF

%check
extdir="$(%{_bindir}/php-config --extension-dir)"
%{_bindir}/php -n -d extension="%{buildroot}${extdir}/pinba.so" -m | grep -qx pinba

%files
%license COPYING
%doc README.md
%{_libdir}/php/modules/pinba.so
%config(noreplace) /etc/php.d/40-pinba.ini

%changelog
* Thu Jul 02 2026 Oleg Ekhlakov <o.ekhlakov@protonmail.com> - 1.5.0-1
- Release 1.5.0; see CHANGELOG.md for details.

* Wed Jun 17 2026 Oleg Ekhlakov <o.ekhlakov@protonmail.com> - 1.4.0-1
- Release 1.4.0; see CHANGELOG.md for details.

* Sun Jun 14 2026 Oleg Ekhlakov <o.ekhlakov@protonmail.com> - 1.3.1-1
- Initial RPM packaging for the Remi php82..php85 SCLs.
