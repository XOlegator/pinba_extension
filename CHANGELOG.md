# Changelog

All notable changes in this fork are tracked here by release automation.

Historical upstream release notes are preserved in `docs/legacy-news.md` (curated) and
`docs/legacy-upstream-news.md` (verbatim upstream `NEWS`).

## [1.7.0](https://github.com/XOlegator/pinba_extension/compare/v1.6.0...v1.7.0) (2026-07-03)


### Features

* **packaging:** AppStream metainfo, CHANGELOG-sourced release notes, and action updates ([#65](https://github.com/XOlegator/pinba_extension/issues/65)) ([12c3ed8](https://github.com/XOlegator/pinba_extension/commit/12c3ed8607ea7c2db34ea9922d06bb4fbfcfc5f7))

## [1.6.0](https://github.com/XOlegator/pinba_extension/compare/v1.5.0...v1.6.0) (2026-07-02)


### Features

* **rpm:** build RPMs for aarch64 too ([#62](https://github.com/XOlegator/pinba_extension/issues/62)) ([638724f](https://github.com/XOlegator/pinba_extension/commit/638724f60fb3eafa5f438fbf38b4040eabf842a6))

## [1.5.0](https://github.com/XOlegator/pinba_extension/compare/v1.4.0...v1.5.0) (2026-07-02)


### Features

* warn instead of silently dropping excess collectors ([#56](https://github.com/XOlegator/pinba_extension/issues/56)) ([b371a59](https://github.com/XOlegator/pinba_extension/commit/b371a59fd89566522080db405d3bd295955e629e))

## [1.4.0](https://github.com/XOlegator/pinba_extension/compare/v1.3.2...v1.4.0) (2026-06-17)


### Features

* **rpm:** add base php-pinba package built against distro-native PHP ([#52](https://github.com/XOlegator/pinba_extension/issues/52)) ([2cecbe9](https://github.com/XOlegator/pinba_extension/commit/2cecbe9e3eb414a23db53376b0173713fe5fbb9c))

## [1.3.2](https://github.com/XOlegator/pinba_extension/compare/v1.3.1...v1.3.2) (2026-06-14)


### Bug Fixes

* **rpm:** pass clean SRPM path to copr-cli and validate COPR_CONFIG ([#45](https://github.com/XOlegator/pinba_extension/issues/45)) ([6b6d125](https://github.com/XOlegator/pinba_extension/commit/6b6d125c509cdd2f64e1041962bff1bdf4f42cf1))

## [1.3.1](https://github.com/XOlegator/pinba_extension/compare/v1.3.0...v1.3.1) (2026-06-14)


### Bug Fixes

* **build:** make memory-footprint guard compile on musl/Alpine ([#39](https://github.com/XOlegator/pinba_extension/issues/39)) ([74a0a63](https://github.com/XOlegator/pinba_extension/commit/74a0a6303c4924ea8618dfe2f3bb3a1372da3751))

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
