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
- open or update a Release PR;
- calculate the next SemVer version:
  - `major`: commit or PR title with `!` or a `BREAKING CHANGE:` footer;
  - `minor`: at least one `feat`;
  - `patch`: `fix`, `perf`, `refactor`, `docs`, `test`, `build`, `ci`, `chore`, `revert`;
- update `CHANGELOG.md`;
- create a tag in the form `vX.Y.Z`;
- create a GitHub Release after the release PR is merged.

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
2. The `release-please` workflow opens or updates a Release PR.
3. The maintainer reviews the proposed version and changelog.
4. After the Release PR is merged, automation creates:
   - tag `vX.Y.Z`
   - GitHub Release
   - updated `CHANGELOG.md`

## Files Used by the Release System

- `.github/workflows/pr-title-conventional.yml`
- `.github/workflows/release-please.yml`
- `release-please-config.json`
- `.release-please-manifest.json`
- `CHANGELOG.md`

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
