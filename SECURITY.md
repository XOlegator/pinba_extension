# Security Policy

## Supported Branch

Security fixes are developed on the active `master` branch of this fork.

## Supported Runtime Matrix

The project targets officially supported PHP branches tracked by this fork's CI and release
policy. As of 2026-06-07, the active matrix is:

- `PHP 8.2`
- `PHP 8.3`
- `PHP 8.4`
- `PHP 8.5`

Older end-of-life PHP branches are not supported for security maintenance in this fork.

## Reporting A Vulnerability

Please do not open a public issue for a suspected security vulnerability.

Report it privately to:

- Oleg Ekhlakov <o.ekhlakov@protonmail.com>

Include, when possible:

- affected PHP version;
- affected extension version or commit;
- reproduction steps;
- whether the issue impacts confidentiality, integrity, availability, or data correctness;
- whether the issue is local-only or remotely triggerable.

## Response Policy

- Acknowledgement target: reasonable best effort.
- Fixes should preserve the existing public contract unless a breaking security mitigation is
  unavoidable.
- Public release notes should be published after a fix is prepared or released.
