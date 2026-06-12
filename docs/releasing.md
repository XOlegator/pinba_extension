# Releases on GitHub

## Goal

This fork uses a semi-automated GitHub release flow:

- version bumps are calculated from Conventional Commits;
- a release PR is created or updated automatically;
- `CHANGELOG.md` is updated automatically;
- a Git tag and GitHub Release are created automatically after the release PR is merged.

## What Is Automated

Configured via GitHub Actions and Release Please:

- monitor pushes to `master`;
- open or update a Release PR **when there is at least one release-triggering commit**;
- calculate the next SemVer version:
  - `major`: commit or PR title with `!` or a `BREAKING CHANGE:` footer;
  - `minor`: at least one `feat`;
  - `patch`: `fix`, `perf`, `revert`, `deps`;
- update `CHANGELOG.md`;
- create a tag in the form `vX.Y.Z`;
- create a GitHub Release after the release PR is merged.

### Commits That Do Not Trigger a Release

With the current Release Please configuration only "user-facing" commit types trigger a release
(`feat`, `fix`, `perf`, `revert`, `deps`, and any breaking change). The following types are
**not** release-triggering on their own and will not open a Release PR by themselves:

- `docs`, `style`, `refactor`, `test`, `build`, `ci`, `chore`.

These commits are still valid and are folded into the changelog of the next release that *is*
triggered by a release-triggering commit — they just do not start a release on their own. For
example, merging a lone `ci(...)` change updates `master` but leaves the released version
unchanged until the next `feat`/`fix` lands.

## Required Project Rules

- Work must be merged into `master` only through Pull Requests.
- PR titles must follow Conventional Commits.
- Commit messages should also follow Conventional Commits.

The repository includes a PR-title validation workflow so non-conforming titles are rejected
before merge.

## Branch Model

- `master` is the release branch.
- Feature, fix, docs, and maintenance work happen in separate branches.
- Release Please watches `master`.

## Release Lifecycle

1. Developers merge regular PRs into `master`.
2. The `release-please` workflow opens or updates a Release PR once `master` contains at least one
   release-triggering commit since the last release (see "Commits That Do Not Trigger a Release").
3. The maintainer reviews the proposed version and changelog.
4. After the Release PR is merged, automation creates:
   - tag `vX.Y.Z`
   - GitHub Release
   - updated `CHANGELOG.md`

## Files Used by the Release System

- `.github/workflows/pr-title-conventional.yml`
- `.github/workflows/release-please.yml`
- `.github/workflows/release-assets.yml`
- `release-please-config.json`
- `.release-please-manifest.json`
- `php_pinba.h` — `PHP_PINBA_VERSION` is bumped automatically on every release via the
  `x-release-please-version` annotation (configured as a `generic` extra-file). This is the only
  in-source version that the release system manages; `package.xml` is intentionally left out.
- `CHANGELOG.md`

## After the Release Is Published

When the Release Please release PR is merged, a `vX.Y.Z` tag and GitHub Release are published.
That publication triggers `release-assets.yml`, which:

- rebuilds the extension from the exact released tag across every supported PHP branch
  (`.github/php-versions.json`) as a final sanity check;
- attaches a reproducible source tarball (`pinba-X.Y.Z.tar.gz`, produced with `git archive`) to
  the GitHub Release.

The workflow can also be run manually via `workflow_dispatch` with a `tag` input (for example
`v1.2.0`) to rebuild or re-attach assets.

A full Debian/Launchpad PPA build is not part of this flow yet — it depends on a `debian/` layer
that does not exist (see `docs/packaging.md`). `release-assets.yml` is structured so a PPA job can
be added alongside the existing ones later.

## One-Time GitHub Repository Settings

Recommended GitHub settings for this automation:

1. `Settings -> Actions -> General`
   - allow GitHub Actions for the repository.
2. `Settings -> Actions -> General -> Workflow permissions`
   - set `Read and write permissions`;
   - enable `Allow GitHub Actions to create and approve pull requests`.
3. `Settings -> Branches`
   - protect `master` with required PRs and required checks.
4. `Settings -> Pull Requests`
   - enable squash merge.

## Baseline Version

The starting point for the new automated release history is stored in
`.release-please-manifest.json`.

This fork keeps legacy upstream history in `docs/legacy-news.md`, while all new fork release
history belongs in `CHANGELOG.md`.
