# Packaging

## Goal

This fork will publish Debian source packages for Launchpad PPA builds targeting Ubuntu 24.04
(`noble`) and Ubuntu 26.04 (`resolute`).

The package layer must preserve the current extension behavior while making installation and
upgrade paths predictable for supported PHP branches.

## Planned Package Model

Source package:

- `php-pinba`

Binary package naming template:

- `php{php_version}-pinba`

Examples:

- `php8.2-pinba`
- `php8.3-pinba`
- `php8.4-pinba`
- `php8.5-pinba`

## Why Versioned Binary Packages

This repository targets multiple supported PHP branches in parallel. Versioned binary packages
are the clearest way to align the extension `.so` with the correct PHP ABI and runtime layout.

That model also matches the fork goal of rebuilding the extension when new supported PHP branches
appear, without overloading a single ambiguous binary package name.

## Planned Debian Layout

The repository is expected to gain a `debian/` directory containing at least:

```text
debian/
├── control
├── rules
├── changelog
├── copyright
├── source/format
├── source/options
├── clean
├── php8.2-pinba.install
├── php8.3-pinba.install
├── php8.4-pinba.install
└── php8.5-pinba.install
```

Additional helper files may be generated per suite to carry the selected PHP version matrix
inside the source package used by Launchpad.

## Machine-Readable Matrix

The current packaging plan is tracked in:

- `.github/packaging-matrix.json`

This file is intended to become the source of truth for:

- Ubuntu suites targeted by packaging automation;
- binary package naming;
- future Launchpad matrix generation;
- gating decisions when a new supported PHP branch is discovered.

## Design Constraints

- A newly discovered PHP branch must not be packaged automatically until CI proves the build.
- Launchpad uploads should happen from reviewed source-package PRs or release workflows, not from
  silent direct pushes.
- Multi-suite packaging should be driven by distro-specific source uploads, as already done in the
  sibling `pinba_engine` project.
- The package layer must install the extension into the correct `extension_dir` and provide the
  matching `/etc/php/<version>/mods-available/pinba.ini` integration.

## Next Packaging Steps

1. Add an initial `debian/` skeleton.
2. Implement package install maps for supported PHP branches.
3. Validate local `dpkg-buildpackage -b`.
4. Add a source-package workflow for Launchpad uploads.
5. Connect packaging rebuilds to the reviewed PHP discovery PR flow.
