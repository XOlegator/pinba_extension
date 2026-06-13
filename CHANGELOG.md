# Changelog

All notable changes in this fork are tracked here by release automation.

Historical upstream release notes are preserved in `docs/legacy-news.md` (curated) and
`docs/legacy-upstream-news.md` (verbatim upstream `NEWS`).

## [1.3.0](https://github.com/XOlegator/pinba_extension/compare/v1.2.3...v1.3.0) (2026-06-13)


### Features

* **pie:** support installation via PIE and Packagist ([#35](https://github.com/XOlegator/pinba_extension/issues/35)) ([13e14d4](https://github.com/XOlegator/pinba_extension/commit/13e14d46c0a9f7aad09fe0b409a509314000d2ca))

## [1.2.3](https://github.com/XOlegator/pinba_extension/compare/v1.2.2...v1.2.3) (2026-06-13)


### Bug Fixes

* **concurrency:** use thread-safe strerror_r in the send error path ([#28](https://github.com/XOlegator/pinba_extension/issues/28)) ([60c4b7e](https://github.com/XOlegator/pinba_extension/commit/60c4b7eae6a7b201c853d335abd3477a2ad21747))

## [1.2.2](https://github.com/XOlegator/pinba_extension/compare/v1.2.1...v1.2.2) (2026-06-12)


### Bug Fixes

* **packaging:** build only PHP versions available per Ubuntu suite ([#26](https://github.com/XOlegator/pinba_extension/issues/26)) ([5b65d88](https://github.com/XOlegator/pinba_extension/commit/5b65d881bad36dcebbac1905914ac9dcbf1c00dd))

## [1.2.1](https://github.com/XOlegator/pinba_extension/compare/v1.2.0...v1.2.1) (2026-06-12)


### Bug Fixes

* **serialization:** prevent buffer leak when realloc fails ([#23](https://github.com/XOlegator/pinba_extension/issues/23)) ([3f46928](https://github.com/XOlegator/pinba_extension/commit/3f469281f345deafc6b624bff56ab5c0bc06835a))

## [1.2.0](https://github.com/XOlegator/pinba_extension/compare/v1.1.2...v1.2.0) (2026-06-07)


### Features

* **packaging:** add packaging plan and machine-readable matrix ([#9](https://github.com/XOlegator/pinba_extension/issues/9)) ([1907764](https://github.com/XOlegator/pinba_extension/commit/1907764fb7ef0df61c22f0120f5a473e579d09f5))


### Bug Fixes

* **ci:** use RELEASE_PLEASE_TOKEN to allow PR creation ([#10](https://github.com/XOlegator/pinba_extension/issues/10)) ([77241ae](https://github.com/XOlegator/pinba_extension/commit/77241ae865d7fc24fbf9268682ee69eb09ac6e64))
* **compat:** modernize hash sort and memory footprint calls ([#3](https://github.com/XOlegator/pinba_extension/issues/3)) ([b358471](https://github.com/XOlegator/pinba_extension/commit/b35847177116c05b8caedd41d57e87d793b9dc68))

## [1.1.2] - 2026-06-07

- Fork baseline established for automated GitHub release management.
