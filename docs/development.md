# Development Workflow

## Branching Rules

- `master` is the protected integration and release branch.
- Never develop directly on `master`.
- Always start work from the latest `master`.
- Every task must be implemented in a dedicated branch.
- Merge into `master` only through a Pull Request.

Recommended branch naming:

- `feat/<short-kebab-description>`
- `fix/<short-kebab-description>`
- `docs/<short-kebab-description>`
- `chore/<short-kebab-description>`
- `refactor/<short-kebab-description>`
- `test/<short-kebab-description>`

Example:

```bash
git checkout master
git pull --ff-only origin master
git checkout -b fix/php84-build
```

## Pull Request Rules

- Open Pull Requests against `master`.
- PR titles must follow Conventional Commits.
- Prefer squash merge to keep history readable.
- Do not merge until required checks pass.

## Commit Message Rules

All commit messages and PR titles must be written in English and follow Conventional Commits.

Common allowed types:

- `feat: ...`
- `fix: ...`
- `perf: ...`
- `refactor: ...`
- `docs: ...`
- `test: ...`
- `build: ...`
- `ci: ...`
- `chore: ...`
- `revert: ...`

Optional scopes are supported:

- `fix(build): ...`
- `docs(readme): ...`
- `ci(release): ...`

Breaking changes:

- `feat!: ...`
- or include a `BREAKING CHANGE:` footer in the commit or PR description

The type also decides whether a change cuts a release. Only extension-code changes
(`pinba.c`, `php_pinba.h`, `config.m4`, `pinba.proto`) should use `feat:`/`fix:`, which bump the
version and tag; packaging, CI, script, docs and test changes use `ci:`/`build:`/`chore:`/`docs:`/
`test:` and leave the version unchanged. See `docs/releasing.md` →
"Version Discipline: What Warrants a Release".

## Release History Policy

- Historical notes from the legacy upstream stay archived in `docs/legacy-news.md` (curated) and
  `docs/legacy-upstream-news.md` (the verbatim upstream `NEWS`).
- All new fork release history is generated into `CHANGELOG.md`.
- Do not maintain release notes by hand; the legacy archives are frozen and changelog entries
  come from Conventional Commits via release automation.

## Repository Hygiene

- Preserve original authorship and license notices.
- Keep public documentation repository-relative and portable.
- Do not commit local experiments, build artifacts, or internal-only planning files.
- Keep workflow files and public markdown clean enough to pass the repository lint jobs.
