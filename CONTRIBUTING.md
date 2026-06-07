# Contributing

## Scope

This fork preserves the Pinba extension userland API and wire protocol while modernizing build,
test, CI, packaging, and supported PHP compatibility.

Do not introduce behavioral changes lightly. If a change affects the PHP API, protobuf payload,
or expected Pinba stack integration, document that explicitly in the Pull Request.

## Workflow

1. Start from the latest `master`.
2. Create a dedicated topic branch.
3. Make focused changes.
4. Run the relevant local checks.
5. Open a Pull Request against `master`.
6. Merge only after required checks pass.

Example:

```bash
git checkout master
git pull --ff-only origin master
git checkout -b fix/php85-compat
```

## Branching

Recommended prefixes:

- `feat/`
- `fix/`
- `refactor/`
- `test/`
- `ci/`
- `build/`
- `docs/`
- `chore/`

## Commit And PR Titles

Commits and Pull Request titles must be written in English and follow Conventional Commits.

Examples:

- `fix(build): adjust PHP 8.5 compatibility guard`
- `test(phpt): add timer regression coverage`
- `ci(lint): validate workflow and markdown files`

## Local Checks

The classic extension flow is still the baseline build interface:

```bash
phpize
./configure --enable-pinba
make -j"$(nproc)"
make test
```

If you have multiple PHP versions installed, validate the change against every supported target
ABI that the change may affect.

## Expectations

- Preserve original authorship and license notices.
- Keep documentation portable and repository-relative.
- Do not commit local build artifacts or internal-only planning files.
- Prefer small, reviewable changes with tests over broad rewrites.
- Treat `pinba.c`, `php_pinba.h`, and protobuf-related files as high-risk areas.

## Related References

- Development workflow: [docs/development.md](docs/development.md)
- Local build flow: [docs/build.md](docs/build.md)
- Release flow: [docs/releasing.md](docs/releasing.md)
- Shared Pinba knowledge base:
  [XOlegator/pinba_engine/knowledge](https://github.com/XOlegator/pinba_engine/tree/master/knowledge)
