# Project Development Instructions

These instructions are read by coding agents that start with fresh context. Prefer explicit, checkable rules over narrative. Keep this file tool-agnostic: describe outcomes, not vendor-specific commands or session features.

## Scope and exploration
- Read this file, then `PROJECT_MAP.md` for orientation.
- Scale exploration to the change: targeted files for fixes; a broader audit for cross-cutting work such as themes, widgets or localization.
- `3rdparty/` is read-only by default. Inspect it only to verify bundled assets or integration compatibility; do not edit vendored code unless a compatibility fix requires it, and call that out explicitly. This scoped rule supersedes any absolute "never index or modify" wording in `PROJECT_MAP.md`.
- Re-read a file immediately before editing if it may have changed; otherwise avoid redundant reads.

## Delivery contract
- One issue per PR. Keep diffs minimal and preserve backward compatibility.
- Target the `alpha` branch from the approved fork. Commit messages, PR titles/bodies and GitHub comments must be in Russian only; no Ukrainian words in GitHub texts. User-facing replies may be in Ukrainian.
- Keep PHP 7.3 compatibility. Do not add runtime dependencies, CDN requests or external services.
- Preserve `en`/`ru`/`uk`. `uk` is a thin alias of `ua.php`; user-facing strings go through language keys.
- Do not ship one-off verification scripts (`scripts/check_*.php`) in the tree. Run checks locally and put the command plus expected output in the PR description; maintainers have explicitly rejected such scripts as end-user files.
- UI changes require Docker screenshots in both themes and at phone/desktop widths. Non-UI changes require a concise verification command and its result in the PR description.

## Coding rules learned in practice
- Themes use CSS tokens with `var(--token, fallback)`; never drop the fallback, because custom themes may predate the token. Dark theme needs its own `--mj-widget-*` overrides. Front-end dark Bootstrap ships `!important` offline rules, so dark overrides must use equal specificity.
- Do not use fixed pixel widths for widgets/cards. Use `box-sizing: border-box`, `min-width: 0`, `max-width`, tokenized icon sizes and touch targets of at least 44 px.
- Icons come from the local Font Awesome layer only. Keep inline `background-image` coming from templates working for user-defined icons.
- Widget refresh must update changed nodes/classes instead of replacing the whole HTML. Debounce WebSocket updates, use the existing batch `get_devices` endpoint, skip hidden tabs and respect `prefers-reduced-motion`.
- Accessibility basics for clickable widgets: `data-state`, `aria-label`, `role="button"`, `tabindex="0"` and a delegated Enter/Space handler that ignores events from inner controls.
- After conflict resolution verify with `git grep -n -E '^<<<<<<< |^>>>>>>> ' -- '*.css' '*.html' '*.php' '*.sample'` and check CSS brace balance. Conflict blocks may have an empty side and no blank line, so any automated cleanup regex must handle that. Never push conflict markers.
- When changing a CSS file, bump every cache-buster reference (`templates/common_header.html`, `pChart/index.php`, `3rdparty/freeboard/index.php`).

## Marketplace publishing (Connect)
- Follow the KB flow: Russian and English descriptions, support URL, tar.gz package URL, image/screenshots and the associated-project option.
- System name must be alphanumeric only. Underscores are rejected with a generic "fill required fields" error (use `googlelocation`, not `google_location`).
- Uncheck "Черновик" to publish. Blog announcements use the `#модули` tag and Russian text; image URLs must be public, so commit screenshots to the module repository when they are missing.
- Keep donation/tip requests gentle and factual.

## Verification and handoff
- Run `php -l` on changed PHP, targeted greps for forbidden patterns, and CSS syntax/brace checks.
- For UI work verify light/dark themes and 375 px / 1440 px widths.
- Summarize changed files, commands run and important decisions. Update `PROJECT_MAP.md` only when architecture or module responsibilities materially change.
- Record reusable discoveries in the PR description or existing docs, not in new shipped files.

## Harness-agnostic notes
- Assume a fresh agent each session: re-read files instead of relying on prior context.
- Use relative paths and standard commands; avoid vendor-specific syntax.
- Keep rules short, deterministic and auditable so different coding agents interpret them the same way.