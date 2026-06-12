# Agent Notes

## Project Context

Pinba Extension is the PHP client extension for the Pinba stack. It instruments PHP requests,
encodes request data with protobuf, and sends UDP packets to Pinba Engine.

This repository is an actively maintained fork of the original project:

- Original upstream: <https://github.com/tony2001/pinba_extension>
- Active fork: <https://github.com/XOlegator/pinba_extension>

## Related Knowledge Base

Before any non-trivial task, review the shared Pinba knowledge base from the sibling project:

1. <https://github.com/XOlegator/pinba_engine/blob/master/knowledge/SCHEMA.md>
2. <https://github.com/XOlegator/pinba_engine/blob/master/knowledge/wiki/index.md>
3. <https://github.com/XOlegator/pinba_engine/blob/master/knowledge/wiki/overview.md>
4. Relevant concept pages from:
   - <https://github.com/XOlegator/pinba_engine/tree/master/knowledge/wiki/concepts>
   - especially `php-pinba-configuration.md`, `pinba-data-flow.md`,
     `pinba-udp-protocol.md`, `github-actions-ppa.md`, and `debian-ppa-packaging.md`

That knowledge base is the canonical reference for packaging, CI, Ubuntu support policy, and
overall Pinba architecture.

## Working Rules

- Preserve the public Pinba protocol and PHP userland API unless the task explicitly changes it.
- Keep legacy behavior stable where possible; modernization should focus on build, test, CI,
  packaging, and compatibility with supported PHP versions.
- Treat `pinba.c`, `php_pinba.h`, and protobuf-related files as high-risk areas.
- Do not edit generated protobuf outputs unless regeneration is part of the task.
- Do not commit build artifacts, local experiments, or internal-only planning documents.
- Keep new project metadata aligned with modern GitHub and Launchpad workflows.

## Build Notes

This is a classic PHP extension repository using `config.m4`. The expected local flow is:

```bash
phpize
./configure
make -j"$(nproc)"
make test
```

When multiple PHP versions are supported, build and test against each target PHP ABI explicitly.

## GitHub Actions

- When adding or editing a workflow, pin every third-party action to its current
  major version. Always verify the latest release before committing instead of
  reusing a version from memory or copying an old example, e.g.:

  ```bash
  gh api repos/actions/checkout/releases/latest --jq .tag_name
  ```

- Reference actions by their major tag (e.g. `actions/checkout@v6`) so they track
  the latest compatible patch, and never introduce a version older than what the
  rest of the repository (or the sibling Pinba repositories) already uses.

## Packaging Direction

- Debian and Launchpad packaging should target Ubuntu 24.04 and Ubuntu 26.04.
- Package naming should remain explicit about the PHP ABI or PHP major/minor version.
- Rebuild automation should react to newly supported PHP releases in Ubuntu/PPAs.
- Preserve original copyright and license notices; add fork-maintainer authorship clearly.

## Commit and PR Hygiene

- Write commit messages, PR titles, and public-facing documentation in English.
- Always branch from the latest `master`; never develop directly on `master`.
- Merge into `master` only through Pull Requests.
- Use Conventional Commits for commit messages and PR titles so release automation can update
  `CHANGELOG.md`.
- Keep changes focused and verifiable.
- Prefer small migrations that unlock future automation over large rewrites without tests.
