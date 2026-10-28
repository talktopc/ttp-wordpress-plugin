# CLAUDE.md - TalkToPC Voice Widget (WordPress Plugin)

## Project Overview

AI-powered voice assistant plugin for WordPress. Visitors have natural voice conversations with AI agents in 40+ languages. Features one-click OAuth setup, automatic AI agent creation from site content, WooCommerce support, and full widget customization. ~6,700 lines of core PHP.

**Version:** 1.9.115

## Tech Stack

- **PHP:** 7.4+
- **Platform:** WordPress 5.0+
- **No build tool** — pure PHP/HTML/CSS/JS

## Entry Point

`talktopc.php` - Main plugin file. Defines constants, includes all modules, handles automatic database migrations on upgrade.

## Project Structure (`/includes`)

| File | LOC | Purpose |
|---|---|---|
| `admin-settings.php` | 489 | Settings registration, sanitizers, admin menu, backend sync hooks |
| `admin-page.php` | 42 | Dashboard page renderer setup |
| `admin-settings-sections.php` | 554 | UI sections (Chat, Advanced, E-commerce) |
| `admin-styles.php` | 1,575 | All admin CSS (animations, grid, responsive) |
| `admin-helpers.php` | 67 | Utility functions for admin pages |
| `ajax-handlers.php` | 1,553 | 15+ AJAX endpoints |
| `oauth.php` | 383 | OAuth 2.0 flow, nonce verification, auto-agent setup |
| `frontend-widget.php` | 528 | Widget enqueue, config builder, page rule matching |
| `migration.php` | 557 | Database migration utility for legacy table upgrades |

### Admin Pages (`/includes/admin-pages/`)

- `dashboard.php` (36 KB) - Connection status, agent selection, settings UI, OAuth flow
- `customization2.php` (50 KB) - Live widget preview, appearance customization
- `appearance.php` (4.8 KB) - Color/position/icon settings
- `page-rules.php` (6.9 KB) - Per-page agent assignment
- `chat.php` (1.6 KB) - Chat-specific settings
- `advanced.php` (1.6 KB) - Advanced configuration

### Admin Scripts (`/includes/admin-scripts/`)

- `dashboard.js.php` (1,081 LOC) - Agent management, API fetch, create/update UI
- `page-rules.js.php` (349 LOC) - Page rules CRUD
- `common.js.php` (101 LOC) - Shared utilities, `wp_localize_script` for AJAX
- `customization2.js` (132 KB) - Widget preview engine, real-time customization

## AJAX Handlers (15 total)

`talktopc_fetch_agents`, `talktopc_fetch_voices`, `talktopc_fetch_credits`, `talktopc_create_agent`, `talktopc_update_agent`, `talktopc_generate_prompt`, `talktopc_save_page_rules`, `talktopc_save_widget_customization2`, `talktopc_get_signed_url` (public/nopriv), and more.

**Every handler needs a nonce and a capability check.** `wp_ajax_` alone only means
"logged in", which on a WooCommerce or membership site is every customer — it is not an
admin gate. The two deliberate exceptions, both load-bearing: `talktopc_get_signed_url` is
registered `_nopriv` because site visitors are not logged in, and its `talktopc_widget_nonce`
is a same-origin speed bump rather than an authentication control (any visitor can read it
out of the page); and `talktopc_test_handler` is a read-only deploy check meant to be called
with curl, so it carries the capability check without a nonce and deliberately does not
report `__FILE__` — an absolute path identifies the hosting account and says nothing about
whether the file is current.

## OAuth Flow (`oauth.php`)

1. User clicks "Connect" → Verify nonce, generate one-time secret, store in 5-min transient
2. Redirect to TalkToPC authorization endpoint
3. Callback verifies nonce + secret with `hash_equals()`, stores credentials
4. Auto-fetch agents; create default if none exist

## Deployment Scripts

Publishing to the WordPress.org plugin directory is `PUBLISH.md`. One command, from a terminal on the laptop: `./deploy_to_svn.sh`. Subversion is not installed here. The script copies the tree into `/home/yinon11/talktopc-wp-plugin-dev/talk-to-pc`, tags the version read from `talktopc.php`, and commits through a temporary `alpine:3.20` container (`docker run --rm -it`, Subversion installed inside, `~/.subversion` mounted). The password prompt is why it has to be a real terminal. GitHub `main` is not the marketplace.

- `deploy_to_svn.sh` - Publish trunk and `tags/<version>` to WordPress.org
- `production_deploy.sh` - Auto-bump patch version, create zip, save to ~/Downloads. Does not upload.
- `upload_all.sh` - Rsync plugin to the server, then `docker cp` into the container (use when the plugin directory is not mounted)
- `dev_sync.sh` - Live dev sync. The Loopitis compose file bind-mounts `/home/ubuntu/loopitis/plugins/talktopc` into `loopitis-wordpress-1` at `wp-content/plugins/talktopc`, and keeps the existing WordPress files volume. `./dev_sync.sh` rsyncs this repo to that directory over `ssh loopitis` and watches for saves. View the site at https://loopitis.com. Do not `docker compose down -v` (that deletes `loop_wp_data`).
- `clear_cache.sh` - Cache management

## Partner embed

Dashboard card **Partner script** (`talktopc_embed_slug`), shown with or without an API key. The admin enters English letters and dashes only, for a registered partner script. After save the name is locked until Edit, and Remove clears it. Saving goes through `talktopc_save_embed_slug` and updates only that option. On the public site, `talktopc_print_partner_embed()` prints one inline script in `wp_head` that skips iframes and loads `https://cdn.talktopc.com/<word>.js`. An empty value prints nothing. That tag, and the normal `talktopc-agent-widget` tag plus its inline init, carry `nowprocket` so WP Rocket Delay JavaScript does not hold them. Browsers ignore the attribute.

## Install & Setup

1. Deploy to `/wp-content/plugins/`
2. Activate the plugin
3. Click **Connect to TalkToPC** (OAuth)

## Conventions & Patterns

- **Namespace:** `talktopc_*` prefix for all functions
- **Security:** `check_admin_referer()`, `wp_verify_nonce()`, `current_user_can()`, `sanitize_*` callbacks; OAuth 2.0 with one-time secrets
- **JS pattern:** PHP-generated `.js.php` files via `wp_add_inline_script()` (WordPress.org compliant)
- **Config:** WordPress Options API (no custom tables; migration provided for legacy data)
- **Error logging:** Guarded with `WP_DEBUG`
- **Distribution:** `.distignore` excludes `.sh`, `.md`, `.git/`, `node_modules`

## Important

**On any change to this repository, update this CLAUDE.md file to reflect the current state of the project.** Keep the project structure, build instructions, and guidelines accurate and up to date.
