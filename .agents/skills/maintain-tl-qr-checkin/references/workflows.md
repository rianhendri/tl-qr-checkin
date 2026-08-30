# TL QR Check-in Specialized Workflows

Read only the section relevant to the current task, plus any prerequisite section it names.

## Compatibility Review

Use this workflow when checking or changing WordPress, PHP, Elementor, or browser compatibility.

1. Record the compatibility claim currently present in the plugin header and code.
2. Verify current upstream requirements and deprecations using official WordPress and Elementor sources when the task depends on current information.
3. Test the declared minimum combination separately from the latest stable combination. Do not assume the oldest Elementor release works with the latest WordPress release.
4. Check plugin activation, dependency notices, Elementor editor loading, frontend widget rendering, QR generation, and PNG export.
5. Treat a raised minimum requirement as a planned compatibility decision. Recommend a major version when it breaks supported installations.
6. Update compatibility documentation only with evidence from tests actually run.

Baseline declared by the current plugin:

- WordPress 6.5+
- PHP 7.4+
- Elementor 3.24.0+

## GitHub Updater

Use this workflow only when the user asks to implement or change WordPress panel updates.

Prerequisites:

- Confirm the final GitHub owner, repository name, visibility, and update asset name.
- Prefer a public repository. Stop and request a credential and threat-model decision before designing authenticated private-repository downloads.
- Read `README.md`, `SECURITY.md`, the main plugin file, and the local roadmap when present.

Implementation requirements:

1. Use the WordPress `Update URI` header and hostname-specific plugin update filter.
2. Restrict update metadata to the configured GitHub repository.
3. Accept only published, non-draft, non-prerelease releases for the stable channel.
4. Validate the version, release URL, and exact expected ZIP asset.
5. Use HTTPS, bounded timeouts and redirects, and WordPress HTTP APIs.
6. Fail closed: malformed data or network failure means no update is offered.
7. Do not force automatic updates or affect frontend rendering.
8. Do not send invitation URLs, guest values, check-in tokens, or the site domain in custom request metadata.
9. Do not store a GitHub token for a public repository.
10. Update README and security claims so admin-side update traffic is disclosed without implying frontend telemetry.

Required tests:

- older local version receives a valid newer update response;
- equal or older remote version is ignored;
- draft, prerelease, invalid version, missing asset, wrong repository, invalid JSON, HTTP error, and timeout are ignored safely;
- update package installs under the expected `tl-qr-checkin/` directory;
- Elementor widget settings remain intact;
- frontend operation remains independent of GitHub availability.

The existing `1.0.0` cannot discover an updater that is not installed yet. Document the one-time manual installation needed for the first updater-enabled release.

## Vendored QR Engine Update

Use this workflow whenever `assets/vendor/qrcode/qrcode.browser.js` changes.

1. Identify the exact upstream repository, version or commit, source file, license, and build method.
2. Review upstream changes and advisories before replacing the bundle.
3. Keep the human-readable source or a durable source link required by the distribution policy.
4. Preserve the MIT license in `assets/vendor/qrcode/LICENSE`.
5. Confirm the bundle contains no terminal, server, telemetry, or network behavior.
6. Update dependency provenance in `SECURITY.md` and later in `THIRD_PARTY_NOTICES.md` when that file exists.
7. Regenerate the vendor checksum and verify `CHECKSUMS.txt`.
8. Test short and long URLs, Unicode content, generated QR scanning, canvas display, and PNG export.

Do not combine a vendor update with unrelated UI or feature changes unless the user explicitly requests both.

## Security Work

Use this workflow for hardening, vulnerability reports, or sensitive-data concerns.

1. Determine whether the request is review-only or authorizes a fix.
2. Trace the affected input, trust boundary, sink, and user capability.
3. Avoid reproducing real guest tokens or personal data in logs, fixtures, screenshots, or reports.
4. Prefer the smallest patch that removes the unsafe path while preserving supported behavior.
5. Add a regression check for the actual invariant, not just the patch wording.
6. Re-review escaping, remote requests, dependencies, and release packaging affected by the patch.
7. Update `SECURITY.md` when the security model or disclosed behavior changes.
8. Recommend a patch release for a backward-compatible fix. Escalate versioning only if remediation must break supported behavior.

Do not publish vulnerability details, create advisories, tag a fix, or release externally unless explicitly authorized. If sensitive details are already present locally, do not echo them unnecessarily in the handoff.

## Release Preparation

Use this workflow only when the user asks to prepare or perform a release.

Preflight:

1. Confirm the target version and release scope.
2. Inspect the full working tree when Git exists and preserve unrelated changes.
3. Confirm all intended runtime changes and documentation are included.
4. Choose PATCH, MINOR, or MAJOR according to `AGENTS.md`.

Prepare:

1. Synchronize the main plugin header and PHP version constant.
2. Keep the README version synchronized until its versioned title is intentionally removed.
3. Update `CHANGELOG.md` once it exists.
4. Regenerate checksums only for covered files that changed.
5. Run PHP syntax, JavaScript syntax, checksum, and all available canonical project checks.
6. Build a ZIP containing a single `tl-qr-checkin/` root directory and no development, secret, Git, AI-agent, test-result, or local planning files.
7. Inspect the ZIP content and install it on staging before publication.
8. Test activation, Elementor editor, frontend widget, QR scanning, PNG export, update discovery where applicable, and rollback.

External boundary:

- Preparing files and a local ZIP does not authorize a commit, push, tag, GitHub Release, or WordPress deployment.
- If external release action is explicitly authorized, prefer a draft release, attach the final ZIP and checksum, verify them, and then publish.
- Never replace an existing published artifact. Publish a new patch version.

## Rollback

Use this workflow when a release must be reversed or a rollback plan is requested.

1. Identify the last known-good published artifact and affected versions.
2. Confirm that rollback will not remove or reinterpret Elementor widget settings.
3. Test manual replacement on staging.
4. Do not move an existing tag or replace an immutable asset.
5. If users require a durable fix, prepare a new patch release rather than treating rollback as the final state.
6. Record the symptom, scope, recovery steps, and prevention action without exposing guest data.

## Handoff Checklist

Report:

- requested outcome and actual result;
- files changed;
- automated and manual validation performed;
- validation not performed and why;
- security, privacy, compatibility, updater, and rollback impact as applicable;
- recommended SemVer level;
- any external action that still requires explicit authorization.
