---
name: maintain-tl-qr-checkin
description: Maintain, review, secure, test, version, release, or update the TL QR Check-in WordPress Elementor plugin in this repository. Use for plugin-specific fixes, compatibility checks, GitHub updater work, vendored QR changes, security hardening, release preparation, and rollback; do not use for unrelated WordPress projects.
---

# Maintain TL QR Check-in

Follow the repository-root `AGENTS.md` as the mandatory policy. Preserve the client-side, lightweight architecture and the user's authorization boundaries.

## Route the Task

- For an ordinary scoped fix or UI adjustment, inspect the relevant runtime files, make the smallest change, update affected documentation and checksums, and run the checks required by `AGENTS.md`.
- For compatibility, updater, vendor, security, release, or rollback work, read the matching section in [references/workflows.md](references/workflows.md) before acting.
- For roadmap or repository-governance work, read `docs/internal/maintenance-roadmap.md` only when it exists. It is local planning context, not a committed source of truth.

## Working Rules

1. Establish current behavior from code and maintained documentation rather than assumptions from an earlier chat.
2. Separate implementation from release preparation. Do not change versions for ordinary maintenance.
3. Keep new production dependencies exceptional. Prefer existing WordPress, Elementor, and browser APIs.
4. Treat URL data as untrusted and potentially sensitive.
5. Verify observable behavior and report any environment-limited checks honestly.
6. Stop before commits, pushes, tags, releases, remote repository changes, or production-site changes unless the user explicitly requested that action.

## Completion

Provide a concise handoff with changed files, tests, untested areas, security and compatibility impact, and the recommended SemVer level for a future release.
