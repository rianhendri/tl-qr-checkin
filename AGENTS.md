# TL QR Check-in Agent Instructions

## Project Scope

TL QR Check-in is a lightweight WordPress plugin that registers an Elementor widget. It reads guest data from the current URL, generates a QR code in the browser, and exports a 1080 x 1920 PNG with the native Canvas API.

Keep the plugin small, predictable, and maintenance-oriented. Do not expand it into a server-side check-in system unless the user explicitly changes the product scope.

Current compatibility baseline:

- WordPress 6.5 or newer.
- PHP 7.4 or newer.
- Elementor 3.24.0 or newer.

Do not raise these minimum versions during ordinary maintenance. Treat a minimum-version increase as a planned breaking compatibility decision.

## Sources of Truth

Read these before changing related behavior:

- `README.md`: installed behavior and user-facing architecture.
- `SECURITY.md`: current security properties and dependency notes.
- `CHECKSUMS.txt`: hashes for the QR vendor, custom JavaScript, and CSS.
- `tl-qr-checkin.php`: plugin metadata, plugin version, and Elementor compatibility.
- `.agents/skills/maintain-tl-qr-checkin/SKILL.md`: project-specific maintenance workflow.

`docs/internal/maintenance-roadmap.md` is a local, Git-ignored planning document. Read it only for roadmap, governance, or repository-setup tasks. Do not make project correctness depend on that uncommitted file.

Runtime code currently consists of:

- `tl-qr-checkin.php`
- `includes/class-tl-qr-checkin-widget.php`
- `templates/qr-checkin.php`
- `assets/js/tl-qr-checkin.js`
- `assets/css/tl-qr-checkin.css`
- `assets/vendor/qrcode/qrcode.browser.js`

## Architecture Invariants

Preserve these unless the user explicitly approves a scoped architecture change:

- QR generation and PNG export remain client-side.
- Do not add plugin-owned database writes, custom tables, options, transients, posts, attachments, or log files.
- Do not add custom AJAX/REST write endpoints or cron jobs.
- Do not add telemetry, analytics, tracking, or guest-data logging.
- Do not load executable frontend code, fonts, or QR images from a remote service.
- Do not use `eval`, `new Function`, obfuscated code, or runtime package loading.
- Load widget assets through Elementor dependency declarations so they are only enqueued where needed.
- Elementor may store widget settings in its own post metadata; do not interfere with that normal Elementor behavior.

A future GitHub updater may make a bounded admin-side update request only when that work is explicitly requested. It must fail closed, never affect the frontend, and be reflected accurately in `README.md` and `SECURITY.md`.

## Data and Security Rules

- Treat all URL query values and Elementor settings as untrusted input.
- Sanitize according to the expected type and escape as late as possible at output.
- Insert visitor-controlled browser text with `textContent`, not `innerHTML`.
- Preserve `esc_html`, `esc_attr`, and `esc_url` boundaries in templates.
- Validate enumerated Elementor values with an allowlist.
- Do not weaken capability checks on administrative notices or future admin operations.
- The QR contains the current full URL without its fragment. Treat guest names and check-in tokens in that URL as potentially sensitive data.
- Never transmit the invitation URL to an update provider, telemetry service, or error logger.
- Do not add a production dependency without explicit approval and a recorded source, exact version or commit, license, and integrity hash.
- When vendored code changes, preserve its license and update its provenance and checksum.

## Change Workflow

1. Inspect the relevant files and, when Git exists, the working-tree status. Preserve unrelated user changes.
2. State the behavioral assumption when requirements are ambiguous but safely inferable.
3. Make the smallest change that satisfies the requested behavior.
4. Update documentation when behavior, compatibility, privacy, security, installation, or release steps change.
5. Run checks proportional to the changed files.
6. Report changed files, checks run, checks not run, and remaining risks.

For diagnosis or review requests, do not implement a fix unless the user also requests a change.

## Version and Release Rules

- Use Semantic Versioning.
- `PATCH`: backward-compatible bug, compatibility, or security fix.
- `MINOR`: backward-compatible feature.
- `MAJOR`: breaking settings, URL behavior, architecture, or minimum-version change.
- Do not bump the plugin version during an ordinary feature or fix task unless the user asks to prepare a release.
- During a release, keep the main plugin `Version` header and `TL_QR_Checkin_Plugin::VERSION` identical.
- The current README title also contains a version; keep it consistent until a planned documentation change removes the version from the title.
- The QR vendor script version passed to `wp_register_script()` is vendor/cache metadata. Do not change it automatically with every plugin release; change it when the vendored QR bundle changes or when a documented cache policy replaces it.
- Never reuse, move, or silently replace a published release tag or ZIP. Publish a new patch version instead.
- Never commit, push, merge, create tags, publish releases, or change a production WordPress site unless the user explicitly authorizes that external action.

## Required Checks

Run all checks relevant to the change. Until canonical project scripts are added, use these direct checks.

For PHP changes:

```bash
php -l tl-qr-checkin.php
php -l includes/class-tl-qr-checkin-widget.php
php -l templates/qr-checkin.php
```

For JavaScript changes:

```bash
node --check assets/js/tl-qr-checkin.js
```

For files covered by `CHECKSUMS.txt`:

```bash
shasum -a 256 -c CHECKSUMS.txt
```

If custom JavaScript, CSS, or the QR vendor changes, regenerate only its corresponding checksum from the final file and then verify the full checksum file. Never invent or manually approximate a hash.

For frontend behavior changes, manually verify the relevant subset:

- Elementor editor preview and frontend rendering.
- Fixed and inline trigger modes.
- Multiple widget instances.
- Missing, malformed, long, and Unicode query values.
- QR content matches the current URL without the fragment.
- PNG export remains 1080 x 1920.
- Same-origin and cross-origin image behavior.
- Keyboard open, close, Escape, focus restoration, and reduced motion.
- Browser console has no new error.
- Frontend makes no new unexpected network request.

Do not claim a browser, WordPress, Elementor, update, or rollback test passed unless it was actually run.

## Documentation Consistency

Update the relevant source of truth in the same change:

- User-visible behavior or setup: `README.md`.
- Security, privacy, network, storage, or dependency behavior: `SECURITY.md`.
- Covered runtime file: `CHECKSUMS.txt`.
- Maintenance governance or roadmap only: local `docs/internal/maintenance-roadmap.md` when present.
- Release history: `CHANGELOG.md` once that file exists.

Do not update claims such as "no network dependency" if a new updater or service makes the statement inaccurate. Distinguish frontend runtime behavior from admin-side update checks.

## Code Review Rules

Flag changes that:

- add an unapproved write path, endpoint, remote runtime request, or tracker;
- place untrusted values into HTML without the correct output escaping;
- use visitor text with an HTML-injection sink;
- expose guest data or check-in tokens in logs or third-party requests;
- enqueue assets globally instead of through the Elementor widget;
- break support for declared minimum versions without an approved versioning decision;
- change a covered file without updating its checksum;
- change vendored code without license and provenance updates;
- bump versions outside release preparation;
- make release or compatibility claims without evidence.

## Handoff

Finish every implementation with a concise report containing:

- outcome and behavior changed;
- files changed;
- validation performed and its result;
- validation not performed and why;
- security, privacy, compatibility, and release impact when relevant;
- whether a version bump is required for the next release.
