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

## Debian Layout

The repository ships a `debian/` directory:

```text
debian/
├── control                     # source php-pinba + php8.{2,3,4,5}-pinba binaries
├── rules                       # manual per-version phpize build loop
├── changelog
├── copyright                   # DEP-5, LGPL-2.1-or-later
├── clean
├── pinba.ini                   # mods-available template (extension=pinba.so)
├── php8.2-pinba.postinst       # phpenmod -v 8.2 pinba   (one pair per version)
├── php8.2-pinba.prerm          # phpdismod -v 8.2 pinba
├── …                           # .postinst/.prerm for 8.3, 8.4, 8.5
└── source/
    ├── format                  # 3.0 (quilt)
    ├── options                 # extend-diff-ignore for phpize build artifacts
    └── lintian-overrides
```

There are **no static `*.install` files**: each PHP version's `extension_dir` is API-numbered
(e.g. `/usr/lib/php/20240924`), so `debian/rules` queries it with `php-config<ver> --extension-dir`
and installs `pinba.so` there, plus `pinba.ini` into `/etc/php/<ver>/mods-available/`. The
matching `phpapi-<api>` dependency is injected into each package's substvars.

The PHP version matrix is overridable per suite via an optional `debian/php-ppa-build.mk`
(`PHP_VERSIONS = 8.3` etc.), mirroring `pinba_engine`; unselected versions are excluded from the
build.

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

## Packaging Steps

1. [x] Add an initial `debian/` skeleton.
2. [x] Implement package install maps for supported PHP branches (rules-driven, see above).
3. [x] Validate the build. `packaging.yml` (`build-debs`) builds all four `php<ver>-pinba` binaries
   on Ubuntu with the `ondrej/php` PPA and runs `lintian`; also validated locally (`.so` loads into
   PHP 8.2–8.5 and reports the correct version).
4. [x] Publish signed source packages to Launchpad — `packaging.yml` (`publish-ppa`), see below.
5. [ ] Connect packaging rebuilds to the reviewed PHP discovery PR flow.

## Publishing to the Launchpad PPA

The `publish-ppa` job in `.github/workflows/packaging.yml` builds the Debian **source** package for
each Ubuntu suite and uploads it to `ppa:xolegator/packages`; Launchpad then builds the binaries.

- **Trigger:** automatically on a published GitHub Release, or manually via `workflow_dispatch`
  (`tag`, `target_suite`, `upload` — `upload: false` builds + signs only, for a dry run — and
  `debian_revision`).
- **Version format:** `{upstream}-{revision}~{suite}1`, e.g. `1.2.0-1~noble1`, `1.2.0-1~resolute1`.
  The `~{suite}` keeps a PPA build sorting below a hypothetical archive upload and orders newer
  Ubuntu suites above older ones.
- **Per suite:** `debian/php-ppa-build.mk` is generated with the suite's `PHP_VERSIONS`, the
  changelog is `dch`-rewritten, the orig tarball is made once with `git archive` (first suite uses
  `-sa`, the rest `-sd`), the `.changes` is signed with the Launchpad GPG key (loopback pinentry)
  and `dput`-uploaded over FTP (`passive_ftp = 1`, for runner NAT) with a retry loop. Uploads are
  sequential so the orig lands before any `-sd` upload references it.
- **Secrets used:** `LAUNCHPAD_GPG_PRIVATE_KEY`, `LAUNCHPAD_GPG_PASSPHRASE`, `LAUNCHPAD_GPG_FINGERPRINT`.

### Required one-time Launchpad setting

The Launchpad build farm must be able to resolve `php8.2/8.4/8.5-dev`. The Ubuntu `noble` archive
ships only `php8.3`, so the **PPA itself must depend on `ppa:ondrej/php`**:

> Launchpad → the PPA → *Edit PPA dependencies* → add `ppa:ondrej/php`.

Without this, only the PHP version present in the target suite's archive will build. If a suite ever
genuinely lacks a version, drop it from that suite in `.github/packaging-matrix.json` (the job reads
its `PHP_VERSIONS` from there per suite).

### After upload

Launchpad needs a few minutes to build; the PPA signing key can take 5–30 minutes to propagate on
the first publish, so `add-apt-repository` may briefly return a key error — wait and retry.
